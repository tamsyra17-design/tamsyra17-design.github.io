<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Exceptions\ShippingUnavailableException;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPlacer
{
    public function __construct(
        private readonly ShippingCalculator $shipping,
        private readonly OrderNumberGenerator $numbers,
        private readonly PaymentManager $payments,
    ) {}

    /**
     * Price, ship and persist an order from validated checkout input.
     *
     * Everything runs in one transaction with the ordered variants locked, so
     * two checkouts cannot both take the last unit. Prices, weights and
     * shipping come from the database, never from the client.
     *
     * @param  array{customer: array<string, mixed>, address: array<string, mixed>, items: list<array{variant_id: int, qty: int}>, payment_method: string}  $data
     *
     * @throws ValidationException when an item is unavailable or short of stock, or the address cannot be shipped to.
     */
    public function place(array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($data, $user): Order {
            $variants = $this->lockVariants($data['items']);
            $lines = $this->priceLines($data['items'], $variants);

            $subtotal = array_sum(array_column($lines, 'line_total_baisa'));
            $weight = array_sum(array_map(
                fn (array $line): int => ($line['variant']->product->weight_grams ?? 0) * $line['qty'],
                $lines,
            ));

            $address = $data['address'];

            try {
                $shipping = $this->shipping->rateFor($address['governorate'], $weight, $subtotal);
            } catch (ShippingUnavailableException) {
                throw ValidationException::withMessages([
                    'address.governorate' => __('We cannot deliver this order to the selected governorate.'),
                ]);
            }

            $order = Order::create([
                'number' => $this->numbers->generate(),
                'user_id' => $user?->id,
                'customer_name' => $data['customer']['name'],
                'phone' => $data['customer']['phone'],
                'email' => $data['customer']['email'] ?? null,
                'address' => [
                    'governorate' => $address['governorate'],
                    'wilayat' => $address['wilayat'],
                    'line1' => $address['line1'],
                    'line2' => $address['line2'] ?? null,
                    'postal_code' => $address['postal_code'] ?? null,
                ],
                'subtotal_baisa' => $subtotal,
                'shipping_baisa' => $shipping,
                'total_baisa' => $subtotal + $shipping,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'payment_method' => $data['payment_method'],
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $variant = $line['variant'];

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'name' => [
                        'product' => $variant->product->name,
                        'variant' => $variant->name,
                    ],
                    'sku' => $variant->sku,
                    'unit_price_baisa' => $line['unit_price_baisa'],
                    'qty' => $line['qty'],
                    'line_total_baisa' => $line['line_total_baisa'],
                ]);

                $variant->decrement('sale_stock', $line['qty']);
            }

            $payment = $order->payments()->create([
                'gateway' => $data['payment_method'],
                'method' => $data['payment_method'],
                'amount_baisa' => $order->total_baisa,
                'status' => Payment::STATUS_PENDING,
            ]);

            $this->payments->driver($data['payment_method'])->create($payment);

            return $order;
        });
    }

    /**
     * Lock the requested variant rows (in id order, to avoid deadlocks between
     * concurrent checkouts) with their products.
     *
     * @param  list<array{variant_id: int, qty: int}>  $items
     * @return Collection<int, ProductVariant>
     */
    private function lockVariants(array $items): Collection
    {
        $ids = array_map(fn (array $item): int => (int) $item['variant_id'], $items);

        return ProductVariant::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->load('product')
            ->keyBy('id');
    }

    /**
     * @param  list<array{variant_id: int, qty: int}>  $items
     * @param  Collection<int, ProductVariant>  $variants
     * @return list<array{variant: ProductVariant, qty: int, unit_price_baisa: int, line_total_baisa: int}>
     */
    private function priceLines(array $items, Collection $variants): array
    {
        $lines = [];
        $errors = [];

        foreach ($items as $index => $item) {
            $variant = $variants->get((int) $item['variant_id']);
            $qty = (int) $item['qty'];
            $unitPrice = $variant?->price_baisa ?? $variant?->product?->price_baisa;

            if ($variant === null || ! $variant->is_active || ! $variant->product?->is_active || $unitPrice === null) {
                $errors["items.{$index}.variant_id"] = __('This item is no longer available.');

                continue;
            }

            if ($variant->sale_stock < $qty) {
                $errors["items.{$index}.qty"] = __('Only :count left in stock.', ['count' => $variant->sale_stock]);

                continue;
            }

            $lines[] = [
                'variant' => $variant,
                'qty' => $qty,
                'unit_price_baisa' => $unitPrice,
                'line_total_baisa' => $unitPrice * $qty,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $lines;
    }
}

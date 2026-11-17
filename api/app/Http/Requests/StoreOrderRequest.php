<?php

namespace App\Http\Requests;

use App\Services\Payments\PaymentManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Guest checkout is allowed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Shape only. Availability, stock, prices and shipping are checked by
     * OrderPlacer under a row lock. Client-supplied totals are not accepted.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:120'],
            'customer.phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9 ]{7,20}$/'],
            'customer.email' => ['nullable', 'email', 'max:255'],

            'address' => ['required', 'array'],
            'address.governorate' => ['required', 'string', 'max:64'],
            'address.wilayat' => ['required', 'string', 'max:64'],
            'address.line1' => ['required', 'string', 'max:255'],
            'address.line2' => ['nullable', 'string', 'max:255'],
            'address.postal_code' => ['nullable', 'string', 'max:16'],

            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.variant_id' => ['required', 'integer', 'distinct'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100'],

            'payment_method' => ['required', 'string', Rule::in(PaymentManager::methods())],
        ];
    }
}

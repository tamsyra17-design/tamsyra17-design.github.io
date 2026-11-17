<?php

namespace App\Services\Payments;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * Payment method => driver class. Online gateways are added in Task 23.
     *
     * @var array<string, class-string<PaymentDriver>>
     */
    private const DRIVERS = [
        'cod' => CodDriver::class,
        'bank_transfer' => BankTransferDriver::class,
    ];

    public function __construct(private readonly Container $container) {}

    public function driver(string $method): PaymentDriver
    {
        $class = self::DRIVERS[$method]
            ?? throw new InvalidArgumentException("Unsupported payment method [{$method}].");

        return $this->container->make($class);
    }

    /**
     * @return list<string>
     */
    public static function methods(): array
    {
        return array_keys(self::DRIVERS);
    }
}

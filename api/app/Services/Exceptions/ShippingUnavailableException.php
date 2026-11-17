<?php

namespace App\Services\Exceptions;

use RuntimeException;

class ShippingUnavailableException extends RuntimeException
{
    public static function forGovernorate(string $governorate): self
    {
        return new self("No active shipping zone for governorate [{$governorate}].");
    }

    public static function forWeight(string $governorate, int $weightGrams): self
    {
        return new self("No shipping rate for {$weightGrams}g to [{$governorate}].");
    }
}

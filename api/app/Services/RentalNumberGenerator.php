<?php

namespace App\Services;

use App\Models\Rental;
use RuntimeException;

class RentalNumberGenerator
{
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    private const ATTEMPTS = 5;

    public function generate(): string
    {
        for ($i = 0; $i < self::ATTEMPTS; $i++) {
            $number = 'RNT-'.now()->format('ymd').'-'.$this->randomSuffix(6);

            if (! Rental::query()->where('number', $number)->exists()) {
                return $number;
            }
        }

        throw new RuntimeException('Could not generate a unique rental number.');
    }

    private function randomSuffix(int $length): int|string
    {
        $max = strlen(self::ALPHABET) - 1;
        $suffix = '';

        for ($i = 0; $i < $length; $i++) {
            $suffix .= self::ALPHABET[random_int(0, $max)];
        }

        return $suffix;
    }
}

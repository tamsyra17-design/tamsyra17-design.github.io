<?php

namespace App\Services;

use App\Models\Order;
use RuntimeException;

class OrderNumberGenerator
{
    /**
     * Unambiguous characters only (no 0/O, 1/I/L), since customers read the
     * number aloud and type it into the tracking form.
     */
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    private const ATTEMPTS = 5;

    /**
     * A new number such as "ORD-261117-7KQ4X9". The unique index on
     * orders.number is the final guard against a race between check and insert.
     */
    public function generate(): string
    {
        for ($i = 0; $i < self::ATTEMPTS; $i++) {
            $number = 'ORD-'.now()->format('ymd').'-'.$this->randomSuffix(6);

            if (! Order::query()->where('number', $number)->exists()) {
                return $number;
            }
        }

        throw new RuntimeException('Could not generate a unique order number.');
    }

    private function randomSuffix(int $length): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $suffix = '';

        for ($i = 0; $i < $length; $i++) {
            $suffix .= self::ALPHABET[random_int(0, $max)];
        }

        return $suffix;
    }
}

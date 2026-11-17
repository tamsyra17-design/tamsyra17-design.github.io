<?php

namespace App\Support;

/**
 * Folds Arabic spelling variants so that search is tolerant of diacritics,
 * tatweel, alef/hamza forms, alef maqsura and ta marbuta.
 */
final class ArabicNormalizer
{
    /**
     * Harakat, superscript alef, Quranic marks and tatweel.
     */
    private const STRIP_PATTERN = '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u';

    private const FOLD_MAP = [
        'أ' => 'ا',
        'إ' => 'ا',
        'آ' => 'ا',
        'ٱ' => 'ا',
        'ى' => 'ي',
        'ة' => 'ه',
    ];

    public static function normalize(string $text): string
    {
        $stripped = preg_replace(self::STRIP_PATTERN, '', $text) ?? $text;

        return strtr($stripped, self::FOLD_MAP);
    }
}

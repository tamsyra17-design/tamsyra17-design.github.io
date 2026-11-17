<?php

use App\Support\ArabicNormalizer;

// api/tests/Unit/ArabicNormalizerTest.php
it('normalizes Arabic spelling variants', function () {
    expect(ArabicNormalizer::normalize('خِنْجَر'))->toBe('خنجر');
    expect(ArabicNormalizer::normalize('خَنجَر'))->toBe('خنجر');
    expect(ArabicNormalizer::normalize('مُصَرَّة'))->toBe('مصره');
});

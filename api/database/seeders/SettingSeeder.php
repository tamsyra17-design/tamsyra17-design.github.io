<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Storefront defaults, grouped as the admin panel shows them. Contact and
     * social values are placeholders for the shop owner to replace.
     *
     * @var array<string, array{group: string, value: mixed}>
     */
    public const DEFAULTS = [
        'store_name' => ['group' => 'general', 'value' => ['ar' => 'تراث عُمان', 'en' => 'Omani Heritage']],
        'currency' => ['group' => 'general', 'value' => 'OMR'],
        'contact_phone' => ['group' => 'contact', 'value' => '+968 0000 0000'],
        'contact_whatsapp' => ['group' => 'contact', 'value' => '+968 0000 0000'],
        'contact_email' => ['group' => 'contact', 'value' => 'info@example.com'],
        'contact_address' => ['group' => 'contact', 'value' => ['ar' => 'مسقط، سلطنة عُمان', 'en' => 'Muscat, Sultanate of Oman']],
        'social_instagram' => ['group' => 'social', 'value' => null],
        'social_x' => ['group' => 'social', 'value' => null],
        'social_tiktok' => ['group' => 'social', 'value' => null],
        'free_shipping_threshold_baisa' => ['group' => 'shipping', 'value' => 50000],
        'payment_methods' => ['group' => 'payments', 'value' => ['cod']],
    ];

    /**
     * Create any missing setting. Existing values are left alone so running
     * the seeder again never undoes an admin's edits.
     */
    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $setting) {
            Setting::firstOrCreate(['key' => $key], $setting);
        }
    }
}

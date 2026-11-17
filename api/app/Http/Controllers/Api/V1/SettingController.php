<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Storefront settings keyed by name: store name, currency, contact,
     * social links, free-shipping threshold and enabled payment methods.
     * Settings in the private group (secrets) are never included.
     */
    public function public(): JsonResponse
    {
        $settings = Setting::query()
            ->public()
            ->orderBy('key')
            ->pluck('value', 'key');

        // Cast to an object so an empty result still serialises as {}.
        return response()->json(['data' => (object) $settings->all()]);
    }
}

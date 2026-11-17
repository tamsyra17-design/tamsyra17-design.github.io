<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_slug' => ['required', 'string'],
            'from' => ['required', 'date', 'after_or_equal:today'],
            'to' => ['required', 'date', 'after:from'],
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:120'],
            'customer.phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9 ]{7,20}$/'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'array'],
            'address.governorate' => ['nullable', 'string', 'max:64'],
            'address.wilayat' => ['nullable', 'string', 'max:64'],
            'address.line1' => ['nullable', 'string', 'max:255'],
            'address.line2' => ['nullable', 'string', 'max:255'],
            'address.postal_code' => ['nullable', 'string', 'max:16'],
            'deposit_mode' => ['nullable', 'string', Rule::in(['hold', 'charge', 'cash', 'bank_transfer'])],
        ];
    }
}

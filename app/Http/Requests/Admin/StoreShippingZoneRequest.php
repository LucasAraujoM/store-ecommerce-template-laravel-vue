<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'provinces' => ['nullable', 'array'],
            'provinces.*' => ['string', 'max:100'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'free_from' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'delivery_estimate' => ['nullable', 'string', 'max:100'],
            'is_pickup' => ['boolean'],
            'pickup_address' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->boolean('is_pickup')),
                'string',
                'max:255',
            ],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $provinces = $this->input('provinces');

        if (is_string($provinces)) {
            $provinces = array_values(array_filter(array_map('trim', explode(',', $provinces))));
        }

        $this->merge([
            'provinces' => $provinces,
            'is_pickup' => $this->boolean('is_pickup'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->integer('sort_order'),
        ]);
    }
}

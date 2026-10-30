<?php

namespace App\Http\Requests\Storefront;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCheckoutRequest extends FormRequest
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
            'customer_first_name' => ['required', 'string', 'max:255'],
            'customer_last_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_dni' => ['nullable', 'string', 'max:20'],

            'shipping_method' => ['required', Rule::enum(ShippingMethod::class)],
            'shipping_zone_id' => [
                'required',
                'integer',
                Rule::exists('shipping_zones', 'id')->where('is_active', true),
            ],
            'shipping_address' => ['exclude_if:shipping_method,pickup', 'required', 'string', 'max:255'],
            'shipping_city' => ['exclude_if:shipping_method,pickup', 'required', 'string', 'max:255'],
            'shipping_province' => ['exclude_if:shipping_method,pickup', 'required', 'string', 'max:255'],
            'shipping_postal_code' => ['exclude_if:shipping_method,pickup', 'required', 'string', 'max:12'],

            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string', 'max:500'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('shipping_zone_id')) {
                    return;
                }

                $zone = ShippingZone::query()->find($this->integer('shipping_zone_id'));

                if ($zone === null) {
                    return;
                }

                $isPickup = $this->input('shipping_method') === ShippingMethod::Pickup->value;

                if ($isPickup && ! $zone->is_pickup) {
                    $validator->errors()->add(
                        'shipping_zone_id',
                        'Elegí una opción de retiro en el local.',
                    );
                }

                if (! $isPickup && $zone->is_pickup) {
                    $validator->errors()->add(
                        'shipping_zone_id',
                        'Elegí una zona de envío a domicilio.',
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'terms.accepted' => 'Tenés que aceptar los términos y condiciones.',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Order;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();
        $subtotal = fake()->numberBetween(2000000, 9000000);
        $shipping = fake()->numberBetween(0, 800000);

        return [
            'user_id' => null,
            'number' => 'LUN-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
            'tracking_code' => 'TRK-'.Str::upper(Str::random(12)),
            'status' => OrderStatus::Pending,
            'payment_method' => PaymentMethod::Transfer,
            'payment_status' => PaymentStatus::Pending,
            'payment_reference' => null,
            'customer_first_name' => $first,
            'customer_last_name' => $last,
            'customer_email' => Str::lower($first.'.'.$last.'@example.com'),
            'customer_phone' => '+54 11 '.fake()->numerify('####-####'),
            'customer_dni' => fake()->numerify('########'),
            'shipping_method' => ShippingMethod::HomeDelivery,
            'shipping_zone_id' => ShippingZone::factory(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_province' => 'Ciudad Autónoma de Buenos Aires',
            'shipping_postal_code' => fake()->numerify('####'),
            'subtotal_cents' => $subtotal,
            'shipping_cost_cents' => $shipping,
            'discount_cents' => 0,
            'total_cents' => $subtotal + $shipping,
            'notes' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Canceled,
            'canceled_at' => now(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingZone>
 */
class ShippingZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'provinces' => [fake()->state()],
            'cost_cents' => fake()->numberBetween(300000, 900000),
            'free_from_cents' => 15000000,
            'delivery_estimate' => '3 a 6 días hábiles',
            'is_pickup' => false,
            'pickup_address' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function pickup(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Retiro en Palermo',
            'provinces' => ['Ciudad Autónoma de Buenos Aires'],
            'cost_cents' => 0,
            'free_from_cents' => null,
            'delivery_estimate' => 'Listo en 24 horas',
            'is_pickup' => true,
            'pickup_address' => 'Av. Santa Fe 3456, Palermo',
        ]);
    }
}

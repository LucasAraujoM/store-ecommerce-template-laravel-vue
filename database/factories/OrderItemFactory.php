<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->numberBetween(1500000, 6000000);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'product_name' => fake()->words(3, true),
            'variant_size' => fake()->randomElement(['S', 'M', 'L']),
            'variant_color' => fake()->randomElement(['Negro', 'Crema', 'Rosé']),
            'sku' => 'LUN-'.fake()->unique()->bothify('????####'),
            'unit_price_cents' => $unitPrice,
            'quantity' => $quantity,
            'total_cents' => $unitPrice * $quantity,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @var array<int, array{name: string, hex: string}>
     */
    private array $colors = [
        ['name' => 'Negro', 'hex' => '#1c1a19'],
        ['name' => 'Crema', 'hex' => '#efe6d8'],
        ['name' => 'Rosé', 'hex' => '#d8a7a1'],
        ['name' => 'Bordó', 'hex' => '#6d2233'],
        ['name' => 'Verde oliva', 'hex' => '#6f7250'],
    ];

    public function definition(): array
    {
        $color = fake()->randomElement($this->colors);
        $size = fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']);

        return [
            'product_id' => Product::factory(),
            'size' => $size,
            'color' => $color['name'],
            'color_hex' => $color['hex'],
            'sku' => 'LUN-'.Str::upper(Str::random(10)),
            'stock' => fake()->numberBetween(0, 20),
            'price_cents' => null,
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => ['stock' => 0]);
    }
}

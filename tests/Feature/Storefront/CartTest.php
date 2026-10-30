<?php

namespace Tests\Feature\Storefront;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_cart_page_can_be_rendered(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/Cart/Index')
                ->has('cart.items', 0)
                ->where('cart.count', 0));
    }

    public function test_guests_can_add_an_available_variant_to_the_cart(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 4]);

        $response = $this->post(route('cart.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast');

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->assertSame(2, (int) Cart::query()->firstOrFail()->items()->sum('quantity'));
    }

    public function test_the_cart_rejects_quantities_above_available_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 2]);

        $response = $this->post(route('cart.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('cart_items', 0);
    }
}

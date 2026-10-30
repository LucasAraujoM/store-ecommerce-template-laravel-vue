<?php

namespace Tests\Feature\Storefront;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_checkout_page_can_be_rendered_with_items_in_the_cart(): void
    {
        [$cart] = $this->cartWithItem(ProductVariant::factory()->create(['stock' => 3]));
        ShippingZone::factory()->create();

        $this->withCookie(CartService::COOKIE, $cart->token)
            ->get(route('checkout.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/Checkout/Create')
                ->has('cart.items', 1)
                ->has('zones', 1));
    }

    public function test_guests_can_place_an_order_with_bank_transfer(): void
    {
        $zone = ShippingZone::factory()->create([
            'cost_cents' => 500000,
            'free_from_cents' => null,
            'is_pickup' => false,
            'is_active' => true,
        ]);

        $product = Product::factory()->create(['price_cents' => 2500000]);

        [$cart, $variant] = $this->cartWithItem(
            ProductVariant::factory()->for($product)->create([
                'stock' => 5,
                'price_cents' => null,
            ]),
            quantity: 2,
        );

        $response = $this->withCookie(CartService::COOKIE, $cart->token)
            ->post(route('checkout.store'), $this->checkoutData($zone));

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas("order_access.{$order->id}", true);

        $this->assertNull($order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(PaymentMethod::Transfer, $order->payment_method);
        $this->assertSame(5000000, $order->subtotal_cents);
        $this->assertSame(500000, $order->shipping_cost_cents);
        $this->assertSame(5500000, $order->total_cents);
        $this->assertCount(1, $order->items);
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_mercadopago_is_rejected_until_an_access_token_is_configured(): void
    {
        config(['store.mercadopago.access_token' => null]);

        $zone = ShippingZone::factory()->create(['is_pickup' => false]);
        [$cart, $variant] = $this->cartWithItem(ProductVariant::factory()->create(['stock' => 5]));

        $data = $this->checkoutData($zone);
        $data['payment_method'] = PaymentMethod::MercadoPago->value;

        $response = $this->withCookie(CartService::COOKIE, $cart->token)
            ->post(route('checkout.store'), $data);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(5, $variant->fresh()->stock);
    }

    public function test_pickup_orders_do_not_require_a_shipping_address_or_charge_shipping(): void
    {
        $zone = ShippingZone::factory()->pickup()->create();
        [$cart] = $this->cartWithItem(ProductVariant::factory()->create(['stock' => 5]));

        $data = $this->checkoutData($zone);
        $data['shipping_method'] = ShippingMethod::Pickup->value;
        unset(
            $data['shipping_address'],
            $data['shipping_city'],
            $data['shipping_province'],
            $data['shipping_postal_code'],
        );

        $this->withCookie(CartService::COOKIE, $cart->token)
            ->post(route('checkout.store'), $data)
            ->assertRedirect();

        $order = Order::query()->firstOrFail();

        $this->assertSame(ShippingMethod::Pickup, $order->shipping_method);
        $this->assertSame(0, $order->shipping_cost_cents);
        $this->assertNull($order->shipping_address);
        $this->assertSame($order->subtotal_cents, $order->total_cents);
    }

    /**
     * @return array{0: Cart, 1: ProductVariant}
     */
    private function cartWithItem(ProductVariant $variant, int $quantity = 1): array
    {
        $cart = Cart::factory()->create();
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return [$cart, $variant];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutData(ShippingZone $zone): array
    {
        return [
            'customer_first_name' => 'Sofía',
            'customer_last_name' => 'Martínez',
            'customer_email' => 'sofia@example.com',
            'customer_phone' => '+54 11 5555-1234',
            'customer_dni' => '35123456',
            'shipping_method' => ShippingMethod::HomeDelivery->value,
            'shipping_zone_id' => $zone->id,
            'shipping_address' => 'Av. Santa Fe 1234',
            'shipping_city' => 'Ciudad Autónoma de Buenos Aires',
            'shipping_province' => 'Ciudad Autónoma de Buenos Aires',
            'shipping_postal_code' => '1425',
            'payment_method' => PaymentMethod::Transfer->value,
            'notes' => 'Timbre B',
            'terms' => true,
        ];
    }
}

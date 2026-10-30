<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_can_be_found_with_its_tracking_code(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.track', ['codigo' => $order->tracking_code]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/Orders/Track')
                ->where('order.tracking_code', $order->tracking_code)
                ->where('order.number', $order->number));
    }

    public function test_the_public_tracking_page_does_not_resolve_order_numbers(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.track', ['codigo' => $order->number]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/Orders/Track')
                ->where('order', null));
    }

    public function test_the_tracking_code_route_shows_the_order(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.track.show', $order->tracking_code))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('store/Orders/Track')
                ->where('order.number', $order->number));
    }

    public function test_the_tracking_code_route_returns_not_found_for_unknown_codes(): void
    {
        $this->get(route('orders.track.show', 'TRK-UNKNOWN'))->assertNotFound();
    }

    public function test_order_confirmation_requires_session_access_or_ownership(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.show', $order))->assertForbidden();

        $this->withSession(["order_access.{$order->id}" => true])
            ->get(route('orders.show', $order))
            ->assertOk();

        $owner = User::factory()->create();
        $ownedOrder = Order::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->get(route('orders.show', $ownedOrder))
            ->assertOk();
    }
}

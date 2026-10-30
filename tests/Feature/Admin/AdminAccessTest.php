<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admins_can_render_the_admin_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->create();
        $this->actingAs($admin);

        $pages = [
            'admin.dashboard' => 'admin/Dashboard',
            'admin.products.index' => 'admin/Products/Index',
            'admin.products.create' => 'admin/Products/Form',
            'admin.categories.index' => 'admin/Categories/Index',
            'admin.categories.create' => 'admin/Categories/Form',
            'admin.shipping-zones.index' => 'admin/ShippingZones/Index',
            'admin.shipping-zones.create' => 'admin/ShippingZones/Form',
            'admin.orders.index' => 'admin/Orders/Index',
        ];

        foreach ($pages as $route => $component) {
            $this->get(route($route))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component($component));
        }

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/Orders/Show')
                ->where('order.number', $order->number));
    }

    public function test_admins_can_update_an_order_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
            'status' => OrderStatus::Paid->value,
            'note' => 'Pago acreditado manualmente',
        ]);

        $response->assertRedirect();

        $order->refresh();

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'created_by' => $admin->id,
            'status' => OrderStatus::Paid->value,
            'note' => 'Pago acreditado manualmente',
        ]);
    }

    public function test_cancelling_an_order_restores_stock(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $variant = ProductVariant::factory()->create(['stock' => 2]);
        $order = Order::factory()->create();

        $order->items()->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'sku' => $variant->sku,
            'unit_price_cents' => 1000000,
            'quantity' => 2,
            'total_cents' => 2000000,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), [
                'status' => OrderStatus::Canceled->value,
            ])
            ->assertRedirect();

        $order->refresh();

        $this->assertSame(OrderStatus::Canceled, $order->status);
        $this->assertNotNull($order->canceled_at);
        $this->assertSame(4, $variant->fresh()->stock);
    }

    public function test_non_admin_users_cannot_update_order_statuses(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'shipping_method' => ShippingMethod::HomeDelivery,
            'payment_method' => PaymentMethod::Transfer,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.orders.status', $order), [
                'status' => OrderStatus::Canceled->value,
            ])
            ->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }
}

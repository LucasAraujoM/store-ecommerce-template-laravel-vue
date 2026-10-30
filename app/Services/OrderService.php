<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create an order from the cart and reserve stock atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function createFromCart(Cart $cart, array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($cart, $data, $user): Order {
            $cart->load('items.productVariant.product');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Tu carrito está vacío.',
                ]);
            }

            $subtotal = 0;
            $lines = [];
            $lockedVariants = [];

            foreach ($cart->items as $item) {
                $variant = ProductVariant::query()
                    ->with('product')
                    ->lockForUpdate()
                    ->find($item->product_variant_id);

                if ($variant === null || ! $variant->is_active || ! $variant->product?->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Una de las prendas de tu carrito ya no está disponible.',
                    ]);
                }

                if ($item->quantity > $variant->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "No hay stock suficiente de {$variant->product->name} ({$variant->label}).",
                    ]);
                }

                $unitPrice = $variant->price_cents ?? $variant->product->price_cents;
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_size' => $variant->size,
                    'variant_color' => $variant->color,
                    'sku' => $variant->sku,
                    'unit_price_cents' => $unitPrice,
                    'quantity' => $item->quantity,
                    'total_cents' => $lineTotal,
                ];

                $lockedVariants[] = [$variant, $item->quantity];
            }

            $shippingMethod = ShippingMethod::from($data['shipping_method']);
            $zone = isset($data['shipping_zone_id'])
                ? ShippingZone::query()->where('is_active', true)->find($data['shipping_zone_id'])
                : null;

            if ($zone === null) {
                throw ValidationException::withMessages([
                    'shipping_zone_id' => 'Elegí una opción de envío válida.',
                ]);
            }

            $shippingCost = $shippingMethod === ShippingMethod::Pickup
                ? 0
                : $zone->costFor($subtotal);

            $total = $subtotal + $shippingCost;

            $order = Order::create([
                'user_id' => $user?->id,
                'number' => $this->generateNumber(),
                'tracking_code' => $this->generateTrackingCode(),
                'status' => OrderStatus::Pending,
                'payment_method' => PaymentMethod::from($data['payment_method']),
                'payment_status' => PaymentStatus::Pending,
                'customer_first_name' => $data['customer_first_name'],
                'customer_last_name' => $data['customer_last_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'customer_dni' => $data['customer_dni'] ?? null,
                'shipping_method' => $shippingMethod,
                'shipping_zone_id' => $zone->id,
                'shipping_address' => $shippingMethod === ShippingMethod::Pickup ? null : ($data['shipping_address'] ?? null),
                'shipping_city' => $shippingMethod === ShippingMethod::Pickup ? null : ($data['shipping_city'] ?? null),
                'shipping_province' => $shippingMethod === ShippingMethod::Pickup ? null : ($data['shipping_province'] ?? null),
                'shipping_postal_code' => $shippingMethod === ShippingMethod::Pickup ? null : ($data['shipping_postal_code'] ?? null),
                'subtotal_cents' => $subtotal,
                'shipping_cost_cents' => $shippingCost,
                'discount_cents' => 0,
                'total_cents' => $total,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create($line);
            }

            foreach ($lockedVariants as [$variant, $quantity]) {
                $variant->decrement('stock', $quantity);
            }

            $order->statusHistories()->create([
                'created_by' => $user?->id,
                'status' => OrderStatus::Pending,
                'note' => 'Pedido creado',
            ]);

            $cart->items()->delete();

            return $order->load('items', 'statusHistories');
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'LUN-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }

    private function generateTrackingCode(): string
    {
        do {
            $code = 'TRK-'.Str::upper(Str::random(12));
        } while (Order::query()->where('tracking_code', $code)->exists());

        return $code;
    }
}

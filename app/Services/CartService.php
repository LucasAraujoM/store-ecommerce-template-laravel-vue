<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public const COOKIE = 'cart_token';

    public function resolve(Request $request): Cart
    {
        $user = $request->user();
        $token = $request->cookie(self::COOKIE);

        if (! is_string($token) || $token === '') {
            $token = $this->newToken($request);
        }

        $cart = Cart::query()->where('token', $token)->first();

        if ($cart === null) {
            $cart = Cart::create([
                'user_id' => $user?->id,
                'token' => $token,
            ]);
        }

        if ($user !== null && $cart->user_id === null) {
            $cart->update(['user_id' => $user->id]);
        }

        // Never reuse a cart that belongs to a different account on a shared device.
        if ($user !== null && $cart->user_id !== null && $cart->user_id !== $user->id) {
            $token = $this->newToken($request);
            $cart = Cart::create([
                'user_id' => $user->id,
                'token' => $token,
            ]);
        }

        return $cart;
    }

    public function add(Cart $cart, ProductVariant $variant, int $quantity): CartItem
    {
        $this->assertAvailable($variant, $quantity);

        $item = $cart->items()->firstOrNew([
            'product_variant_id' => $variant->id,
        ]);

        $newQuantity = (int) $item->quantity + $quantity;
        $this->assertAvailable($variant, $newQuantity);

        $item->quantity = $newQuantity;
        $item->save();

        return $item;
    }

    public function update(Cart $cart, CartItem $item, int $quantity): void
    {
        abort_unless($item->cart_id === $cart->id, 403);

        $variant = $item->productVariant;
        $this->assertAvailable($variant, $quantity);

        $item->update(['quantity' => $quantity]);
    }

    public function remove(Cart $cart, CartItem $item): void
    {
        abort_unless($item->cart_id === $cart->id, 403);

        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function count(Cart $cart): int
    {
        return (int) $cart->items()->sum('quantity');
    }

    /**
     * @return array{
     *     items: array<int, array<string, mixed>>,
     *     count: int,
     *     subtotal_cents: int,
     *     free_shipping_from_cents: int
     * }
     */
    public function summary(Cart $cart): array
    {
        $items = $cart->items()
            ->with(['productVariant.product.images', 'productVariant.product.category'])
            ->get();

        $lines = [];
        $subtotal = 0;
        $count = 0;

        foreach ($items as $item) {
            $variant = $item->productVariant;
            $product = $variant->product;

            if ($product === null) {
                continue;
            }

            $unitPrice = $variant->price_cents ?? $product->price_cents;
            $lineTotal = $unitPrice * $item->quantity;
            $subtotal += $lineTotal;
            $count += $item->quantity;

            $lines[] = [
                'id' => $item->id,
                'variant_id' => $variant->id,
                'quantity' => $item->quantity,
                'stock' => $variant->stock,
                'unit_price_cents' => $unitPrice,
                'total_cents' => $lineTotal,
                'size' => $variant->size,
                'color' => $variant->color,
                'color_hex' => $variant->color_hex,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'product_image' => $product->primary_image_url,
                'available' => $variant->is_active
                    && $product->is_active
                    && $variant->stock >= $item->quantity,
            ];
        }

        return [
            'items' => $lines,
            'count' => $count,
            'subtotal_cents' => $subtotal,
            'free_shipping_from_cents' => (int) config('store.free_shipping_from_cents'),
        ];
    }

    private function assertAvailable(ProductVariant $variant, int $quantity): void
    {
        $variant->loadMissing('product');

        if (! $variant->is_active || ! $variant->product?->is_active) {
            throw ValidationException::withMessages([
                'quantity' => 'Esta prenda ya no está disponible.',
            ]);
        }

        if ($quantity < 1 || $quantity > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Solo hay {$variant->stock} unidades disponibles de esta variante.",
            ]);
        }
    }

    private function newToken(Request $request): string
    {
        $token = (string) Str::uuid();

        Cookie::queue(self::COOKIE, $token, 60 * 24 * 30);
        $request->cookies->set(self::COOKIE, $token);

        return $token;
    }
}

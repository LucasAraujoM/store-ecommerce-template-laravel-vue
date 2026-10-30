<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreCartItemRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function index(Request $request): Response
    {
        $cart = $this->carts->resolve($request);

        return Inertia::render('store/Cart/Index', [
            'cart' => $this->carts->summary($cart),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $cart = $this->carts->resolve($request);
        $variant = ProductVariant::query()->findOrFail($request->integer('product_variant_id'));

        $this->carts->add($cart, $variant, $request->integer('quantity'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Agregaste la prenda al carrito.',
        ]);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->carts->resolve($request);

        $this->carts->update($cart, $cartItem, $request->integer('quantity'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Actualizamos tu carrito.',
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->carts->resolve($request);

        $this->carts->remove($cart, $cartItem);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Quitamos la prenda del carrito.',
        ]);
    }

    public function clear(Request $request): RedirectResponse
    {
        $cart = $this->carts->resolve($request);

        $this->carts->clear($cart);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Vaciaste el carrito.',
        ]);
    }
}

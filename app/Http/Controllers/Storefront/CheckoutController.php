<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreCheckoutRequest;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $carts,
        private OrderService $orders,
    ) {}

    public function create(Request $request): Response|RedirectResponse
    {
        $cart = $this->carts->resolve($request);
        $summary = $this->carts->summary($cart);

        if ($summary['count'] === 0) {
            return redirect()->route('cart.index')->with('toast', [
                'type' => 'info',
                'message' => 'Tu carrito está vacío.',
            ]);
        }

        $zones = ShippingZone::query()->active()->get();
        $user = $request->user();
        [$firstName, $lastName] = $this->splitName($user?->name);

        return Inertia::render('store/Checkout/Create', [
            'cart' => $summary,
            'zones' => $zones,
            'mercadopagoEnabled' => filled(config('store.mercadopago.access_token')),
            'customer' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user?->email ?? '',
                'phone' => $user?->phone ?? '',
                'dni' => $user?->dni ?? '',
            ],
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $cart = $this->carts->resolve($request);
        $data = $request->validated();

        if ($data['payment_method'] === PaymentMethod::MercadoPago->value
            && blank(config('store.mercadopago.access_token'))) {
            throw ValidationException::withMessages([
                'payment_method' => 'MercadoPago todavía no está configurado. Elegí transferencia bancaria.',
            ]);
        }

        $order = $this->orders->createFromCart($cart, $data, $request->user());

        $request->session()->put("order_access.{$order->id}", true);

        return redirect()
            ->route('orders.show', $order)
            ->with('toast', [
                'type' => 'success',
                'message' => '¡Recibimos tu pedido!',
            ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(?string $name): array
    {
        if (blank($name)) {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) === 1) {
            return [$parts[0], ''];
        }

        $firstName = array_shift($parts);

        return [$firstName, implode(' ', $parts)];
    }
}

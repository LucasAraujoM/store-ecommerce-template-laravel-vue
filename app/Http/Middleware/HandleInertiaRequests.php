<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(private CartService $carts) {}

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $cart = $this->carts->resolve($request);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'cartCount' => $this->carts->count($cart),
            'categories' => Category::active()
                ->get(['id', 'name', 'slug'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->all(),
            'store' => [
                'name' => config('store.name'),
                'currency' => config('store.currency'),
                'free_shipping_from_cents' => (int) config('store.free_shipping_from_cents'),
                'contact' => config('store.contact'),
                'transfer' => config('store.transfer'),
                'mercadopago_enabled' => filled(config('store.mercadopago.access_token')),
            ],
            'flash' => [
                'toast' => fn (): mixed => $request->session()->get('toast'),
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OrderTrackingController extends Controller
{
    public function index(Request $request): Response
    {
        $code = trim((string) $request->query('codigo', ''));
        $order = $code !== '' ? $this->findOrder($code) : null;

        return Inertia::render('store/Orders/Track', [
            'code' => $code,
            'order' => $order,
        ]);
    }

    public function search(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:64'],
        ]);

        return redirect()->route('orders.track', [
            'codigo' => Str::upper(trim($validated['codigo'])),
        ]);
    }

    public function show(string $code): Response
    {
        $order = $this->findOrder($code);

        abort_if($order === null, 404);

        return Inertia::render('store/Orders/Track', [
            'code' => Str::upper($code),
            'order' => $order,
        ]);
    }

    private function findOrder(string $code): ?Order
    {
        $code = Str::upper(trim($code));

        return Order::query()
            ->where('tracking_code', $code)
            ->with(['items', 'statusHistories.author:id,name', 'shippingZone'])
            ->first();
    }
}

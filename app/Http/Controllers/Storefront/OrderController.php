<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): Response
    {
        $hasSessionAccess = (bool) $request->session()->get("order_access.{$order->id}");
        $ownsOrder = $request->user() !== null && $order->user_id === $request->user()->id;

        abort_unless($hasSessionAccess || $ownsOrder, 403);

        $order->load(['items', 'statusHistories.author:id,name', 'shippingZone']);

        return Inertia::render('store/Orders/Show', [
            'order' => $order,
            'transfer' => config('store.transfer'),
        ]);
    }
}

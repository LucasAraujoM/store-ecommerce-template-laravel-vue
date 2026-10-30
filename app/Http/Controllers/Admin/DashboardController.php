<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $revenue = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total_cents');

        $stats = [
            'revenue_cents' => (int) $revenue,
            'orders_today' => Order::query()->whereDate('created_at', today())->count(),
            'orders_pending' => Order::query()->where('status', OrderStatus::Pending)->count(),
            'orders_total' => Order::query()->count(),
            'products_active' => \App\Models\Product::query()->where('is_active', true)->count(),
            'low_stock_count' => ProductVariant::query()
                ->where('is_active', true)
                ->where('stock', '<=', 3)
                ->count(),
        ];

        $recentOrders = Order::query()
            ->withCount('items')
            ->latest()
            ->take(8)
            ->get();

        $lowStock = ProductVariant::query()
            ->with('product:id,name,slug')
            ->where('is_active', true)
            ->where('stock', '<=', 3)
            ->orderBy('stock')
            ->take(8)
            ->get();

        $topProducts = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as units'))
            ->groupBy('product_name')
            ->orderByDesc('units')
            ->take(5)
            ->get();

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStock' => $lowStock,
            'topProducts' => $topProducts,
        ]);
    }
}

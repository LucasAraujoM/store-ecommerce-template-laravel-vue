<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $orders = Order::query()
            ->withCount('items')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%'.$filters['q'].'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('number', 'like', $term)
                        ->orWhere('tracking_code', 'like', $term)
                        ->orWhere('customer_email', 'like', $term)
                        ->orWhere('customer_last_name', 'like', $term);
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => OrderStatus::manageable(),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'items',
            'statusHistories.author:id,name',
            'shippingZone',
            'user:id,name,email',
        ]);

        return Inertia::render('admin/Orders/Show', [
            'order' => $order,
            'statuses' => OrderStatus::manageable(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $newStatus = OrderStatus::from($data['status']);

        if ($order->status === OrderStatus::Canceled && $newStatus !== OrderStatus::Canceled) {
            throw ValidationException::withMessages([
                'status' => 'Un pedido cancelado no puede volver a otro estado.',
            ]);
        }

        DB::transaction(function () use ($request, $order, $newStatus, $data): void {
            if ($newStatus === OrderStatus::Canceled && $order->status !== OrderStatus::Canceled) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id !== null) {
                        ProductVariant::query()
                            ->whereKey($item->product_variant_id)
                            ->increment('stock', $item->quantity);
                    }
                }

                $order->canceled_at = now();
            }

            if ($newStatus === OrderStatus::Paid) {
                $order->payment_status = PaymentStatus::Paid;
                $order->paid_at ??= now();
            }

            if ($newStatus === OrderStatus::Shipped) {
                $order->shipped_at ??= now();
            }

            if ($newStatus === OrderStatus::Delivered) {
                $order->delivered_at ??= now();
            }

            $order->status = $newStatus;
            $order->save();

            $order->statusHistories()->create([
                'created_by' => $request->user()->id,
                'status' => $newStatus,
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Estado del pedido actualizado.',
        ]);
    }
}

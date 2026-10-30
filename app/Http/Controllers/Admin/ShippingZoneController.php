<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneRequest;
use App\Http\Requests\Admin\UpdateShippingZoneRequest;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShippingZoneController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/ShippingZones/Index', [
            'zones' => ShippingZone::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/ShippingZones/Form', [
            'zone' => null,
        ]);
    }

    public function store(StoreShippingZoneRequest $request): RedirectResponse
    {
        ShippingZone::create($this->attributes($request->validated()));

        return redirect()
            ->route('admin.shipping-zones.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Zona de envío creada.',
            ]);
    }

    public function edit(ShippingZone $shippingZone): Response
    {
        return Inertia::render('admin/ShippingZones/Form', [
            'zone' => $shippingZone,
        ]);
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $shippingZone): RedirectResponse
    {
        $shippingZone->update($this->attributes($request->validated()));

        return redirect()
            ->route('admin.shipping-zones.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Zona de envío actualizada.',
            ]);
    }

    public function destroy(ShippingZone $shippingZone): RedirectResponse
    {
        $shippingZone->delete();

        return redirect()
            ->route('admin.shipping-zones.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Zona de envío eliminada.',
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'provinces' => $data['provinces'] ?? null,
            'cost_cents' => (int) round(((float) $data['cost']) * 100),
            'free_from_cents' => filled($data['free_from'] ?? null)
                ? (int) round(((float) $data['free_from']) * 100)
                : null,
            'delivery_estimate' => $data['delivery_estimate'] ?? null,
            'is_pickup' => (bool) $data['is_pickup'],
            'pickup_address' => $data['pickup_address'] ?? null,
            'is_active' => (bool) $data['is_active'],
            'sort_order' => (int) $data['sort_order'],
        ];
    }
}

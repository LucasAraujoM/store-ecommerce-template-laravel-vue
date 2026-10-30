<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Retiro en Palermo',
                'provinces' => ['Ciudad Autónoma de Buenos Aires'],
                'cost_cents' => 0,
                'free_from_cents' => null,
                'delivery_estimate' => 'Listo en 24 horas',
                'is_pickup' => true,
                'pickup_address' => 'Av. Santa Fe 3456, Palermo, CABA',
                'sort_order' => 1,
            ],
            [
                'name' => 'CABA',
                'provinces' => ['Ciudad Autónoma de Buenos Aires'],
                'cost_cents' => 490000,
                'free_from_cents' => 15000000,
                'delivery_estimate' => '1 a 2 días hábiles',
                'is_pickup' => false,
                'pickup_address' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gran Buenos Aires',
                'provinces' => ['Buenos Aires'],
                'cost_cents' => 690000,
                'free_from_cents' => 18000000,
                'delivery_estimate' => '2 a 4 días hábiles',
                'is_pickup' => false,
                'pickup_address' => null,
                'sort_order' => 3,
            ],
            [
                'name' => 'Interior del país',
                'provinces' => [
                    'Córdoba', 'Santa Fe', 'Mendoza', 'Tucumán', 'Salta', 'Entre Ríos',
                    'Misiones', 'Chaco', 'Corrientes', 'Jujuy', 'Río Negro', 'Neuquén',
                    'San Luis', 'San Juan', 'La Pampa', 'La Rioja', 'Catamarca',
                    'Santiago del Estero', 'Formosa', 'Chubut', 'Santa Cruz',
                    'Tierra del Fuego',
                ],
                'cost_cents' => 990000,
                'free_from_cents' => 25000000,
                'delivery_estimate' => '3 a 6 días hábiles',
                'is_pickup' => false,
                'pickup_address' => null,
                'sort_order' => 4,
            ],
        ];

        foreach ($zones as $zone) {
            ShippingZone::updateOrCreate(
                ['name' => $zone['name']],
                [
                    ...$zone,
                    'is_active' => true,
                ],
            );
        }
    }
}

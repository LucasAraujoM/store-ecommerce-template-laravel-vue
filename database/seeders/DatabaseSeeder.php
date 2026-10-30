<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@lunaria.test'],
            [
                'name' => 'Admin Lunaria',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'cliente@lunaria.test'],
            [
                'name' => 'Sofía Martínez',
                'phone' => '+54 11 5555-1234',
                'dni' => '35123456',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
        );

        $this->call([
            ShippingZoneSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}

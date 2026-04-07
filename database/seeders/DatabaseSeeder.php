<?php

namespace Database\Seeders;

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
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            RestaurantTableSeeder::class,
            TableSessionSeeder::class,
            CategorySeeder::class,
            DishSeeder::class,
            DiscountSeeder::class,
            ComboSeeder::class,
            ComboDishSeeder::class,
            CartOrderSeeder::class,
            CartOrderItemSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,
        ]);
    }
}

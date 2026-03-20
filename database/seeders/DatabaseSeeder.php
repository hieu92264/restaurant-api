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
            TableAreaSeeder::class,
            RestaurantTableSeeder::class,
            TableSessionSeeder::class,
            ItemTypeSeeder::class,
            CookingMethodSeeder::class,
            MenuCategorySeeder::class,
            MenuItemSeeder::class,
            MenuItemVariantSeeder::class,
            OptionGroupSeeder::class,
            OptionValueSeeder::class,
            VariantOptionGroupSeeder::class,
            ComboSeeder::class,
            ComboGroupSeeder::class,
            ComboGroupItemSeeder::class,
            CartOrderSeeder::class,
            CartOrderItemSeeder::class,
            CartOrderItemOptionSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Combo;
use Illuminate\Database\Seeder;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Combo::withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'CB_TRUA_A'],
            [
                'name' => 'Combo trưa 2 người',
                'remark' => 'Gồm 2 món chính và 2 nước uống cố định',
                'base_price' => 169000,
                'is_active' => true,
            ]
        );
    }
}

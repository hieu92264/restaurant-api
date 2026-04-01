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
                'remark' => 'Gồm 1 món chính và 1 nước cho mỗi khách',
                'base_price' => 169000,
                'is_active' => true,
                'is_customize_allowed' => true,
            ]
        );
    }
}

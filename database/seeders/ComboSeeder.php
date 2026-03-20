<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
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
            ['code' => 'CB_TRUA_A'],
            [
                'name' => 'Combo trua 2 nguoi',
                'remark' => 'Gom 1 mon chinh va 1 nuoc cho moi khach',
                'base_price' => 169000,
                'is_active' => ActiveStatus::YES,
                'is_customize_allowed' => true,
            ]
        );
    }
}

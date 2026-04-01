<?php

namespace Database\Seeders;

use App\Models\Combo;
use App\Models\ComboGroup;
use Illuminate\Database\Seeder;

class ComboGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $combo = Combo::withoutGlobalScopes()->where('slug', 'CB_TRUA_A')->first();

        $groups = [
            ['group_name' => 'Chọn món chính', 'min_select' => 1, 'max_select' => 2, 'display_order' => 1],
            ['group_name' => 'Chọn nước uống', 'min_select' => 1, 'max_select' => 2, 'display_order' => 2],
        ];

        foreach ($groups as $group) {
            ComboGroup::withoutGlobalScopes()->updateOrCreate(
                [
                    'combo_id' => $combo?->id,
                    'group_name' => $group['group_name'],
                ],
                $group + ['is_active' => true]
            );
        }
    }
}

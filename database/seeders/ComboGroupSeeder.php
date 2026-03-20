<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
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
        $combo = Combo::withoutGlobalScopes()->where('code', 'CB_TRUA_A')->first();

        $groups = [
            ['group_name' => 'Chon mon chinh', 'min_select' => 1, 'max_select' => 2, 'display_order' => 1],
            ['group_name' => 'Chon nuoc uong', 'min_select' => 1, 'max_select' => 2, 'display_order' => 2],
        ];

        foreach ($groups as $group) {
            ComboGroup::withoutGlobalScopes()->updateOrCreate(
                [
                    'combo_id' => $combo?->id,
                    'group_name' => $group['group_name'],
                ],
                $group + ['is_active' => ActiveStatus::YES]
            );
        }
    }
}

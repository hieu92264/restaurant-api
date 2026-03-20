<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\SelectionType;
use App\Models\OptionGroup;
use Illuminate\Database\Seeder;

class OptionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ['code' => 'SUGAR_LEVEL', 'name' => 'Muc duong', 'selection_type' => SelectionType::SINGLE, 'is_required' => false, 'min_select' => 0, 'max_select' => 1, 'display_order' => 1],
            ['code' => 'ICE_LEVEL', 'name' => 'Muc da', 'selection_type' => SelectionType::SINGLE, 'is_required' => false, 'min_select' => 0, 'max_select' => 1, 'display_order' => 2],
            ['code' => 'TOPPING', 'name' => 'Topping them', 'selection_type' => SelectionType::MULTIPLE, 'is_required' => false, 'min_select' => 0, 'max_select' => 3, 'display_order' => 3],
        ];

        foreach ($groups as $group) {
            OptionGroup::withoutGlobalScopes()->updateOrCreate(
                ['code' => $group['code']],
                $group + ['is_active' => ActiveStatus::YES]
            );
        }
    }
}

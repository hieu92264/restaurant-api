<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
use App\Models\OptionGroup;
use App\Models\OptionValue;
use Illuminate\Database\Seeder;

class OptionValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $values = [
            ['group_code' => 'SUGAR_LEVEL', 'value_code' => 'SUGAR_30', 'value_name' => '30% duong', 'price_delta' => 0],
            ['group_code' => 'SUGAR_LEVEL', 'value_code' => 'SUGAR_50', 'value_name' => '50% duong', 'price_delta' => 0],
            ['group_code' => 'SUGAR_LEVEL', 'value_code' => 'SUGAR_100', 'value_name' => '100% duong', 'price_delta' => 0],
            ['group_code' => 'ICE_LEVEL', 'value_code' => 'ICE_0', 'value_name' => 'Khong da', 'price_delta' => 0],
            ['group_code' => 'ICE_LEVEL', 'value_code' => 'ICE_50', 'value_name' => '50% da', 'price_delta' => 0],
            ['group_code' => 'ICE_LEVEL', 'value_code' => 'ICE_100', 'value_name' => '100% da', 'price_delta' => 0],
            ['group_code' => 'TOPPING', 'value_code' => 'BLACK_PEARL', 'value_name' => 'Tran chau den', 'price_delta' => 8000],
            ['group_code' => 'TOPPING', 'value_code' => 'PUDDING', 'value_name' => 'Pudding', 'price_delta' => 8000],
            ['group_code' => 'TOPPING', 'value_code' => 'CHEESE_FOAM', 'value_name' => 'Kem cheese', 'price_delta' => 12000],
        ];

        foreach ($values as $value) {
            $group = OptionGroup::withoutGlobalScopes()->where('code', $value['group_code'])->first();

            OptionValue::withoutGlobalScopes()->updateOrCreate(
                [
                    'option_group_id' => $group?->id,
                    'value_code' => $value['value_code'],
                ],
                [
                    'value_name' => $value['value_name'],
                    'price_delta' => $value['price_delta'],
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}

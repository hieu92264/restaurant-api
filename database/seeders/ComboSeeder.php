<?php

namespace Database\Seeders;

use App\Common\Constants\ComboTag;
use App\Common\Constants\DayInWeek;
use App\Models\Combo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $combos = [
            [
                'name' => 'Da Tiec Thuong Uyen',
                'remark' => 'Suon bo My nuong, ruou vang do, salad huu co, trang mieng lava.',
                'combo_image' => null,
                'combo_price' => 890000,
                'max_use_times' => 20,
                'tag' => ComboTag::HOT,
                'days_in_week' => [
                    DayInWeek::MONDAY,
                    DayInWeek::TUESDAY,
                    DayInWeek::WEDNESDAY,
                    DayInWeek::THURSDAY,
                    DayInWeek::FRIDAY,
                ],
                'start_time' => '18:00',
                'end_time' => '22:00',
                'start_at' => '2026-04-01 00:00:00',
                'end_at' => '2026-06-30 23:59:59',
                'is_active' => true,
            ],
            [
                'name' => 'Huong Vi Dai Duong',
                'remark' => 'Tom hum bo lo, hau song, cha ca la vong, soup hai san.',
                'combo_image' => null,
                'combo_price' => 1550000,
                'max_use_times' => 12,
                'tag' => ComboTag::SEASONAL,
                'days_in_week' => [
                    DayInWeek::MONDAY,
                    DayInWeek::TUESDAY,
                    DayInWeek::WEDNESDAY,
                    DayInWeek::THURSDAY,
                    DayInWeek::FRIDAY,
                    DayInWeek::SATURDAY,
                    DayInWeek::SUNDAY,
                ],
                'start_time' => '10:00',
                'end_time' => '14:00',
                'start_at' => '2026-04-01 00:00:00',
                'end_at' => '2026-06-30 23:59:59',
                'is_active' => true,
            ],
            [
                'name' => 'Business Lunch',
                'remark' => 'Mon chinh tu chon, ca phe hoac tra, trai cay mua vu.',
                'combo_image' => null,
                'combo_price' => 245000,
                'max_use_times' => 60,
                'tag' => ComboTag::FAST,
                'days_in_week' => [
                    DayInWeek::MONDAY,
                    DayInWeek::TUESDAY,
                    DayInWeek::WEDNESDAY,
                    DayInWeek::THURSDAY,
                    DayInWeek::FRIDAY,
                ],
                'start_time' => '11:30',
                'end_time' => '13:30',
                'start_at' => '2026-04-01 00:00:00',
                'end_at' => '2026-06-30 23:59:59',
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon Tea Set',
                'remark' => 'Macarons, banh mousse, tra Anh Quoc, scones.',
                'combo_image' => null,
                'combo_price' => 499000,
                'max_use_times' => 30,
                'tag' => ComboTag::RELAX,
                'days_in_week' => [
                    DayInWeek::SATURDAY,
                    DayInWeek::SUNDAY,
                ],
                'start_time' => '14:00',
                'end_time' => '17:00',
                'start_at' => '2026-04-01 00:00:00',
                'end_at' => '2026-06-30 23:59:59',
                'is_active' => true,
            ],
        ];

        foreach ($combos as $item) {
            Combo::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $this->makeSlug($item['name'])],
                $item
            );
        }
    }

    private function makeSlug(string $name): string
    {
        return Str::slug($name);
    }
}

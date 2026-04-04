<?php

namespace Database\Seeders;

use App\Common\Constants\ComboTag;
use App\Common\Constants\DayInWeek;
use App\Models\Combo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaignStart = Carbon::create(2026, 4, 1, 0, 0, 0);
        $campaignEnd = Carbon::create(2026, 6, 30, 23, 59, 59);

        $combos = [
            [
                'name' => 'Da Tiec Thuong Uyen',
                'remark' => 'Suon bo My nuong, ruou vang do, salad huu co, trang mieng lava.',
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
                'start_time' => Carbon::create(2026, 4, 1, 18, 0, 0),
                'end_time' => Carbon::create(2026, 4, 1, 22, 0, 0),
                'start_at' => $campaignStart,
                'end_at' => $campaignEnd,
                'is_active' => true,
            ],
            [
                'name' => 'Huong Vi Dai Duong',
                'remark' => 'Tom hum bo lo, hau song, cha ca la vong, soup hai san.',
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
                'start_time' => Carbon::create(2026, 4, 1, 10, 0, 0),
                'end_time' => Carbon::create(2026, 4, 1, 14, 0, 0),
                'start_at' => $campaignStart,
                'end_at' => $campaignEnd,
                'is_active' => true,
            ],
            [
                'name' => 'Business Lunch',
                'remark' => 'Mon chinh tu chon, ca phe hoac tra, trai cay mua vu.',
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
                'start_time' => Carbon::create(2026, 4, 1, 11, 30, 0),
                'end_time' => Carbon::create(2026, 4, 1, 13, 30, 0),
                'start_at' => $campaignStart,
                'end_at' => $campaignEnd,
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon Tea Set',
                'remark' => 'Macarons, banh mousse, tra Anh Quoc, scones.',
                'combo_price' => 499000,
                'max_use_times' => 30,
                'tag' => ComboTag::RELAX,
                'days_in_week' => [
                    DayInWeek::SATURDAY,
                    DayInWeek::SUNDAY,
                ],
                'start_time' => Carbon::create(2026, 4, 1, 14, 0, 0),
                'end_time' => Carbon::create(2026, 4, 1, 17, 0, 0),
                'start_at' => $campaignStart,
                'end_at' => $campaignEnd,
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

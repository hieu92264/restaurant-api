<?php

namespace Database\Seeders;

use App\Common\Constants\ActiveStatus;
use App\Models\CookingMethod;
use Illuminate\Database\Seeder;

class CookingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['code' => 'STIR_FRIED', 'name' => 'Mon xao'],
            ['code' => 'FRIED', 'name' => 'Mon chien'],
            ['code' => 'GRILLED', 'name' => 'Mon nuong'],
            ['code' => 'BOILED', 'name' => 'Mon luoc'],
        ];

        foreach ($methods as $method) {
            CookingMethod::withoutGlobalScopes()->updateOrCreate(
                ['code' => $method['code']],
                $method + ['is_active' => ActiveStatus::YES]
            );
        }
    }
}

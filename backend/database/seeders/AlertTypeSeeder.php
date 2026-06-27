<?php

namespace Database\Seeders;

use App\Models\AlertType;
use Illuminate\Database\Seeder;

class AlertTypeSeeder extends Seeder
{
    /**
     * Reference data — safe to re-run (idempotent by slug).
     */
    public function run(): void
    {
        $types = [
            'rain' => 'Rain',
            'storm' => 'Storm',
            'extreme_heat' => 'Extreme Heat',
            'extreme_cold' => 'Extreme Cold',
            'strong_wind' => 'Strong Wind',
            'snow' => 'Snow',
            'uv_index' => 'High UV Index',
            'air_quality' => 'Poor Air Quality',
            'fog' => 'Fog',
        ];

        $sortOrder = 0;

        foreach ($types as $slug => $name) {
            AlertType::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $sortOrder++],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\EventStatus;
use Illuminate\Database\Seeder;

class EventStatusSeeder extends Seeder
{
    /**
     * Reference data — safe to re-run (idempotent by slug).
     */
    public function run(): void
    {
        $statuses = [
            'draft' => 'Draft',
            'published' => 'Published',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
        ];

        $sortOrder = 0;

        foreach ($statuses as $slug => $name) {
            EventStatus::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $sortOrder++],
            );
        }
    }
}

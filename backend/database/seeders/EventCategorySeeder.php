<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Reference data — safe to re-run (idempotent by slug).
     */
    public function run(): void
    {
        $categories = [
            'sports' => 'Sports',
            'music' => 'Music',
            'conference' => 'Conference',
            'festival' => 'Festival',
            'community' => 'Community',
            'business' => 'Business',
            'education' => 'Education',
            'food_drink' => 'Food & Drink',
            'arts_culture' => 'Arts & Culture',
            'other' => 'Other',
        ];

        $sortOrder = 0;

        foreach ($categories as $slug => $name) {
            EventCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $sortOrder++],
            );
        }
    }
}

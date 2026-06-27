<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('now', '+2 months');
        $endsAt = (clone $startsAt)->modify('+'.$this->faker->numberBetween(2, 8).' hours');

        return [
            'user_id' => User::factory(),
            'category_id' => EventCategory::firstOrCreate(['slug' => 'other'], ['name' => 'Other'])->id,
            'status_id' => EventStatus::firstOrCreate(['slug' => 'draft'], ['name' => 'Draft'])->id,
            'name' => rtrim($this->faker->sentence(3), '.'),
            'description' => $this->faker->optional()->paragraph(),
            'city' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'coordinates' => new Point((float) $this->faker->latitude(), (float) $this->faker->longitude(), Srid::WGS84),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'timezone' => $this->faker->timezone(),
            'attendees' => $this->faker->numberBetween(0, 500),
            'is_outdoor' => $this->faker->boolean(),
        ];
    }

    public function outdoor(): static
    {
        return $this->state(fn () => ['is_outdoor' => true]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status_id' => EventStatus::firstOrCreate(['slug' => 'published'], ['name' => 'Published'])->id,
        ]);
    }
}

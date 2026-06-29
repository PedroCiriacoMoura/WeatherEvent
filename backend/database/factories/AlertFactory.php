<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\AlertType;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'alert_type_id' => AlertType::firstOrCreate(['slug' => 'rain'], ['name' => 'Rain'])->id,
            'message' => $this->faker->sentence(),
            'locale' => $this->faker->randomElement(['en', 'pt', 'es']),
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}

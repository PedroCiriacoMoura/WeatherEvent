<?php

namespace Tests\Feature\Event;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\RequiresSpatialDatabase;
use Tests\TestCase;

class EventWeatherTest extends TestCase
{
    use RefreshDatabase;
    use RequiresSpatialDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipWithoutSpatialSupport();
    }

    private function fakeOpenWeather(): void
    {
        Http::fake([
            '*/data/2.5/weather*' => Http::response([
                'main' => ['temp' => 22, 'feels_like' => 21, 'humidity' => 50, 'pressure' => 1012],
                'wind' => ['speed' => 3.0],
                'clouds' => ['all' => 10],
                'weather' => [['main' => 'Clear', 'description' => 'clear sky']],
            ]),
            '*/data/2.5/forecast*' => Http::response([
                'list' => [[
                    'dt' => now()->addDay()->getTimestamp(),
                    'main' => ['temp' => 23, 'feels_like' => 22, 'humidity' => 45],
                    'wind' => ['speed' => 4.0],
                    'pop' => 0.1,
                    'weather' => [['main' => 'Clear', 'description' => 'clear sky']],
                ]],
            ]),
            '*/data/2.5/air_pollution*' => Http::response([
                'list' => [['main' => ['aqi' => 2], 'components' => ['pm2_5' => 5.0]]],
            ]),
        ]);
    }

    public function test_it_returns_weather_for_event(): void
    {
        $this->fakeOpenWeather();
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->outdoor()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/events/{$event->id}/weather")
            ->assertOk()
            ->assertJsonPath('data.current.temperature', 22)
            ->assertJsonPath('data.air_quality.aqi', 2);
    }

    public function test_it_returns_health_score(): void
    {
        $this->fakeOpenWeather();
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->outdoor()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/events/{$event->id}/health-score")
            ->assertOk()
            ->assertJsonStructure(['data' => ['score', 'rating', 'factors']]);
    }

    public function test_it_returns_recommendations(): void
    {
        $this->fakeOpenWeather();
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->outdoor()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/events/{$event->id}/recommendations")
            ->assertOk()
            ->assertJsonStructure(['data' => ['locale', 'summary', 'items']]);
    }

    public function test_non_owner_cannot_view_weather(): void
    {
        $this->fakeOpenWeather();
        $event = Event::factory()->for(User::factory())->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/events/{$event->id}/weather")->assertForbidden();
    }

    public function test_it_returns_502_when_provider_fails(): void
    {
        Http::fake(['*' => Http::response([], 500)]);
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/events/{$event->id}/weather")->assertStatus(502);
    }
}

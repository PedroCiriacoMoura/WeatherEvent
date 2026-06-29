<?php

namespace Tests\Unit\Services;

use App\Exceptions\OpenWeatherException;
use App\Models\Event;
use App\Services\OpenWeatherService;
use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    use RefreshDatabase;

    private function event(): Event
    {
        $event = new Event([
            'city' => 'Lisbon',
            'country' => 'PT',
            'is_outdoor' => true,
        ]);
        $event->coordinates = new Point(38.72, -9.13, Srid::WGS84);
        $event->starts_at = now()->addDay();
        $event->ends_at = now()->addDay()->addHours(3);

        return $event;
    }

    private function fakeSuccess(): void
    {
        Http::fake([
            '*/data/2.5/weather*' => Http::response(['main' => ['temp' => 21], 'wind' => ['speed' => 2], 'weather' => [['main' => 'Clear', 'description' => 'clear sky']]]),
            '*/data/2.5/forecast*' => Http::response(['list' => []]),
            '*/data/2.5/air_pollution*' => Http::response(['list' => [['main' => ['aqi' => 2], 'components' => []]]]),
        ]);
    }

    public function test_it_builds_and_persists_weather(): void
    {
        $this->fakeSuccess();

        $payload = (new WeatherService(new OpenWeatherService))->forEvent($this->event());

        $this->assertSame(21, $payload['current']['temperature']);
        $this->assertDatabaseHas('weather_cache', ['city' => 'Lisbon', 'country' => 'PT']);
    }

    public function test_it_falls_back_to_cache_when_provider_fails(): void
    {
        $this->fakeSuccess();
        $service = new WeatherService(new OpenWeatherService);
        $service->forEvent($this->event());

        Cache::flush();
        Http::fake(['*' => Http::response([], 500)]);

        $payload = $service->forEvent($this->event());

        $this->assertSame(21, $payload['current']['temperature']);
    }

    public function test_it_throws_when_no_cache_available(): void
    {
        Http::fake(['*' => Http::response([], 500)]);

        $this->expectException(OpenWeatherException::class);

        (new WeatherService(new OpenWeatherService))->forEvent($this->event());
    }
}

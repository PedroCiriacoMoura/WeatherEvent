<?php

namespace Tests\Unit\Services;

use App\Exceptions\OpenWeatherException;
use App\Services\OpenWeatherService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWeatherServiceTest extends TestCase
{
    public function test_it_fetches_current_weather(): void
    {
        Http::fake(['*/data/2.5/weather*' => Http::response(['main' => ['temp' => 19]])]);

        $result = (new OpenWeatherService)->currentWeather(38.7, -9.1);

        $this->assertSame(19, $result['main']['temp']);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/data/2.5/weather')
            && $request['lat'] == 38.7
            && $request['lon'] == -9.1);
    }

    public function test_it_caches_repeated_requests(): void
    {
        Http::fake(['*' => Http::response(['main' => ['temp' => 19]])]);

        $service = new OpenWeatherService;
        $service->currentWeather(38.7, -9.1);
        $service->currentWeather(38.7, -9.1);

        Http::assertSentCount(1);
    }

    public function test_it_returns_first_geocoding_match(): void
    {
        Http::fake(['*/geo/1.0/direct*' => Http::response([
            ['name' => 'Lisbon', 'lat' => 38.72, 'lon' => -9.13, 'country' => 'PT'],
        ])]);

        $result = (new OpenWeatherService)->geocode('Lisbon', 'PT');

        $this->assertSame('Lisbon', $result['name']);
    }

    public function test_it_throws_on_provider_error(): void
    {
        Http::fake(['*' => Http::response([], 500)]);

        $this->expectException(OpenWeatherException::class);

        (new OpenWeatherService)->currentWeather(38.7, -9.1);
    }
}

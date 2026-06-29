<?php

namespace App\Services;

use App\Exceptions\OpenWeatherException;
use App\Models\Event;
use App\Models\WeatherCache;
use Illuminate\Support\Carbon;

class WeatherService
{
    public function __construct(private readonly OpenWeatherService $client) {}

    public function forEvent(Event $event): array
    {
        $latitude = $event->coordinates->latitude;
        $longitude = $event->coordinates->longitude;

        try {
            $payload = $this->build($event, $latitude, $longitude);
            $this->persist($event, $payload);

            return $payload;
        } catch (OpenWeatherException $e) {
            $cached = $this->fromFallback($event);

            if ($cached !== null) {
                return $cached;
            }

            throw $e;
        }
    }

    private function build(Event $event, float $latitude, float $longitude): array
    {
        $current = $this->client->currentWeather($latitude, $longitude);
        $forecast = $this->client->forecast($latitude, $longitude);
        $airQuality = $this->client->airQuality($latitude, $longitude);

        return [
            'location' => [
                'city' => $event->city,
                'country' => $event->country,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'current' => $this->normalizeCurrent($current),
            'forecast' => $this->normalizeForecast($forecast, $event),
            'air_quality' => $this->normalizeAirQuality($airQuality),
            'units' => (string) config('services.openweather.units', 'metric'),
            'retrieved_at' => Carbon::now()->toIso8601String(),
        ];
    }

    private function normalizeCurrent(array $current): array
    {
        return [
            'temperature' => data_get($current, 'main.temp'),
            'feels_like' => data_get($current, 'main.feels_like'),
            'humidity' => data_get($current, 'main.humidity'),
            'pressure' => data_get($current, 'main.pressure'),
            'wind_speed' => data_get($current, 'wind.speed'),
            'wind_gust' => data_get($current, 'wind.gust'),
            'clouds' => data_get($current, 'clouds.all'),
            'rain' => (float) data_get($current, 'rain.1h', 0),
            'snow' => (float) data_get($current, 'snow.1h', 0),
            'condition' => data_get($current, 'weather.0.main'),
            'description' => data_get($current, 'weather.0.description'),
        ];
    }

    private function normalizeForecast(array $forecast, Event $event): array
    {
        $list = data_get($forecast, 'list', []);
        $start = $event->starts_at?->getTimestamp();
        $end = $event->ends_at?->getTimestamp() ?? $start;

        $slots = collect($list)
            ->filter(function (array $slot) use ($start, $end) {
                if ($start === null) {
                    return true;
                }

                $dt = (int) data_get($slot, 'dt');

                return $dt >= $start && $dt <= ($end ?? $start);
            })
            ->values();

        if ($slots->isEmpty() && $start !== null) {
            $slots = collect($list)
                ->sortBy(fn (array $slot) => abs(((int) data_get($slot, 'dt')) - $start))
                ->take(1)
                ->values();
        }

        return $slots->map(fn (array $slot) => [
            'at' => Carbon::createFromTimestampUTC((int) data_get($slot, 'dt'))->toIso8601String(),
            'temperature' => data_get($slot, 'main.temp'),
            'feels_like' => data_get($slot, 'main.feels_like'),
            'humidity' => data_get($slot, 'main.humidity'),
            'wind_speed' => data_get($slot, 'wind.speed'),
            'wind_gust' => data_get($slot, 'wind.gust'),
            'rain' => (float) data_get($slot, 'rain.3h', 0),
            'snow' => (float) data_get($slot, 'snow.3h', 0),
            'precipitation_probability' => data_get($slot, 'pop'),
            'condition' => data_get($slot, 'weather.0.main'),
            'description' => data_get($slot, 'weather.0.description'),
        ])->all();
    }

    private function normalizeAirQuality(array $airQuality): array
    {
        return [
            'aqi' => data_get($airQuality, 'list.0.main.aqi'),
            'components' => data_get($airQuality, 'list.0.components', []),
        ];
    }

    private function persist(Event $event, array $payload): void
    {
        WeatherCache::updateOrCreate(
            ['city' => $event->city, 'country' => $event->country],
            [
                'payload' => $payload,
                'expires_at' => Carbon::now()->addSeconds((int) config('services.openweather.cache_ttl', 3600)),
            ],
        );
    }

    private function fromFallback(Event $event): ?array
    {
        $cache = WeatherCache::query()
            ->where('city', $event->city)
            ->where('country', $event->country)
            ->first();

        if ($cache === null || $cache->isExpired()) {
            return null;
        }

        return $cache->payload;
    }
}

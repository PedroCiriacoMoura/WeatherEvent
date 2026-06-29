<?php

namespace App\Services;

use App\Exceptions\OpenWeatherException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenWeatherService
{
    public function currentWeather(float $latitude, float $longitude): array
    {
        return $this->remember('current', compact('latitude', 'longitude'), fn () => $this->get('/data/2.5/weather', [
            'lat' => $latitude,
            'lon' => $longitude,
            'units' => $this->units(),
            'lang' => $this->language(),
        ]));
    }

    public function forecast(float $latitude, float $longitude): array
    {
        return $this->remember('forecast', compact('latitude', 'longitude'), fn () => $this->get('/data/2.5/forecast', [
            'lat' => $latitude,
            'lon' => $longitude,
            'units' => $this->units(),
            'lang' => $this->language(),
        ]));
    }

    public function airQuality(float $latitude, float $longitude): array
    {
        return $this->remember('air_quality', compact('latitude', 'longitude'), fn () => $this->get('/data/2.5/air_pollution', [
            'lat' => $latitude,
            'lon' => $longitude,
        ]));
    }

    public function geocode(string $city, ?string $country = null): ?array
    {
        $query = $country !== null ? "{$city},{$country}" : $city;

        $results = $this->remember('geocode', ['q' => $query], fn () => $this->get('/geo/1.0/direct', [
            'q' => $query,
            'limit' => 1,
        ]));

        return $results[0] ?? null;
    }

    private function remember(string $endpoint, array $params, callable $callback): array
    {
        return Cache::remember($this->cacheKey($endpoint, $params), $this->ttl(), $callback);
    }

    private function get(string $path, array $query): array
    {
        try {
            $response = $this->client()->get($path, array_merge($query, ['appid' => $this->apiKey()]));
        } catch (ConnectionException $e) {
            throw OpenWeatherException::unreachable($e);
        }

        if ($response->failed()) {
            throw OpenWeatherException::requestFailed($response->status());
        }

        try {
            return (array) $response->json();
        } catch (RequestException $e) {
            throw OpenWeatherException::requestFailed($response->status());
        }
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->timeout((int) config('services.openweather.timeout', 10))
            ->acceptJson();
    }

    private function cacheKey(string $endpoint, array $params): string
    {
        ksort($params);

        return 'openweather:'.$endpoint.':'.md5(json_encode($params));
    }

    private function ttl(): int
    {
        return (int) config('services.openweather.cache_ttl', 3600);
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.openweather.base_url'), '/');
    }

    private function apiKey(): string
    {
        return (string) config('services.openweather.key');
    }

    private function units(): string
    {
        return (string) config('services.openweather.units', 'metric');
    }

    private function language(): string
    {
        return (string) config('services.openweather.language', 'en');
    }
}

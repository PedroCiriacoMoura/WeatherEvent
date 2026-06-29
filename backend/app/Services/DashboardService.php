<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\WeatherCache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Aggregates dashboard statistics and chart series for a user.
 *
 * Weather-derived figures (average temperature, risk distribution) are computed
 * from the {@see WeatherCache} table — keyed by city/country — so the dashboard
 * never triggers N OpenWeather calls. When no fresh cache exists for an event's
 * location the figure is simply omitted (null / not counted) and the frontend
 * renders an empty state.
 */
class DashboardService
{
    public function __construct(private readonly HealthScoreService $healthScore) {}

    /**
     * @return array{
     *     total_events:int,
     *     upcoming_events:int,
     *     critical_events:int,
     *     average_temperature:float|null,
     *     total_events_delta:float|null,
     *     upcoming_events_delta:float|null
     * }
     */
    public function stats(User $user): array
    {
        $now = Carbon::now();

        $totalEvents = $user->events()->count();
        $upcoming = $user->events()->where('starts_at', '>', $now)->get();

        $caches = $this->cacheMap($upcoming);

        $temperatures = [];
        $critical = 0;

        foreach ($upcoming as $event) {
            $payload = $caches->get($this->locationKey($event->city, $event->country));

            if ($payload === null) {
                continue;
            }

            $temperature = data_get($payload, 'current.temperature');
            if (is_numeric($temperature)) {
                $temperatures[] = (float) $temperature;
            }

            $rating = $this->healthScore->scoreFor($event, $payload)['rating'] ?? null;
            if (in_array($rating, ['poor', 'hazardous'], true)) {
                $critical++;
            }
        }

        return [
            'total_events' => $totalEvents,
            'upcoming_events' => $upcoming->count(),
            'critical_events' => $critical,
            'average_temperature' => $temperatures === []
                ? null
                : round(array_sum($temperatures) / count($temperatures), 1),
            'total_events_delta' => $this->createdDelta($user, $now),
            'upcoming_events_delta' => $this->upcomingDelta($user, $now),
        ];
    }

    /**
     * @return array{
     *     temperature:list<array{at:string,value:float}>,
     *     rain:list<array{at:string,value:float}>,
     *     humidity:list<array{at:string,value:float}>,
     *     risk:list<array{at:string,excellent:int,good:int,fair:int,poor:int,hazardous:int}>
     * }
     */
    public function charts(User $user): array
    {
        $upcoming = $user->events()->where('starts_at', '>', Carbon::now())->get();
        $caches = $this->cacheMap($upcoming);

        // Daily aggregates keyed by Y-m-d.
        $temperature = [];
        $humidity = [];
        $rain = [];
        $risk = [];

        foreach ($upcoming as $event) {
            $payload = $caches->get($this->locationKey($event->city, $event->country));

            if ($payload === null) {
                continue;
            }

            $slots = data_get($payload, 'forecast', []);
            if ($slots === []) {
                $current = data_get($payload, 'current');
                if ($current !== null) {
                    $slots = [array_merge($current, ['at' => $event->starts_at?->toIso8601String()])];
                }
            }

            foreach ($slots as $slot) {
                $day = $this->dayKey($slot['at'] ?? $event->starts_at?->toIso8601String());
                if ($day === null) {
                    continue;
                }

                $this->pushSample($temperature, $day, data_get($slot, 'temperature'));
                $this->pushSample($humidity, $day, data_get($slot, 'humidity'));
                $this->pushSample($rain, $day, data_get($slot, 'rain'), sum: true);
            }

            // Risk distribution bucketed by the event's start day.
            $rating = $this->healthScore->scoreFor($event, $payload)['rating'] ?? null;
            $startDay = $this->dayKey($event->starts_at?->toIso8601String());
            if ($rating !== null && $startDay !== null) {
                $risk[$startDay] ??= [
                    'excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0, 'hazardous' => 0,
                ];
                $risk[$startDay][$rating]++;
            }
        }

        return [
            'temperature' => $this->series($temperature),
            'rain' => $this->series($rain),
            'humidity' => $this->series($humidity),
            'risk' => $this->riskSeries($risk),
        ];
    }

    /**
     * Loads non-expired weather caches for the locations of the given events.
     *
     * @param  Collection<int,Event>  $events
     * @return Collection<string,array<string,mixed>>
     */
    private function cacheMap(Collection $events): Collection
    {
        $cities = $events->pluck('city')->filter()->unique()->values();

        if ($cities->isEmpty()) {
            return collect();
        }

        return WeatherCache::query()
            ->whereIn('city', $cities)
            ->get()
            ->reject(fn (WeatherCache $cache) => $cache->isExpired())
            ->mapWithKeys(fn (WeatherCache $cache) => [
                $this->locationKey($cache->city, $cache->country) => $cache->payload,
            ]);
    }

    private function locationKey(?string $city, ?string $country): string
    {
        return mb_strtolower(trim((string) $city)).'|'.mb_strtolower(trim((string) $country));
    }

    private function dayKey(?string $iso): ?string
    {
        if ($iso === null) {
            return null;
        }

        try {
            return Carbon::parse($iso)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Accumulates a numeric sample into a per-day bucket.
     *
     * @param  array<string,array{sum:float,count:int}>  $bucket
     */
    private function pushSample(array &$bucket, string $day, mixed $value, bool $sum = false): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $bucket[$day] ??= ['sum' => 0.0, 'count' => 0];
        $bucket[$day]['sum'] += (float) $value;
        $bucket[$day]['count']++;

        if ($sum) {
            // For cumulative metrics (rain) the divisor is forced to 1 on read.
            $bucket[$day]['count'] = 1;
        }
    }

    /**
     * @param  array<string,array{sum:float,count:int}>  $bucket
     * @return list<array{at:string,value:float}>
     */
    private function series(array $bucket): array
    {
        ksort($bucket);

        return collect($bucket)
            ->map(fn (array $entry, string $day) => [
                'at' => $day,
                'value' => round($entry['sum'] / max(1, $entry['count']), 1),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string,array<string,int>>  $bucket
     * @return list<array{at:string,excellent:int,good:int,fair:int,poor:int,hazardous:int}>
     */
    private function riskSeries(array $bucket): array
    {
        ksort($bucket);

        return collect($bucket)
            ->map(fn (array $entry, string $day) => array_merge(['at' => $day], $entry))
            ->values()
            ->all();
    }

    private function createdDelta(User $user, Carbon $now): ?float
    {
        $current = $user->events()
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->count();

        $previous = $user->events()
            ->whereBetween('created_at', [$now->copy()->subDays(60), $now->copy()->subDays(30)])
            ->count();

        return $this->percentChange($previous, $current);
    }

    private function upcomingDelta(User $user, Carbon $now): ?float
    {
        $next30 = $user->events()
            ->whereBetween('starts_at', [$now, $now->copy()->addDays(30)])
            ->count();

        $following = $user->events()
            ->whereBetween('starts_at', [$now->copy()->addDays(30), $now->copy()->addDays(60)])
            ->count();

        return $this->percentChange($following, $next30);
    }

    private function percentChange(int $previous, int $current): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}

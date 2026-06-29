<?php

namespace Tests\Unit\Services;

use App\Models\Event;
use App\Services\HealthScoreService;
use Tests\TestCase;

class HealthScoreServiceTest extends TestCase
{
    private function weather(array $current, ?int $aqi = 1): array
    {
        return [
            'current' => $current,
            'forecast' => [],
            'air_quality' => ['aqi' => $aqi],
        ];
    }

    public function test_pleasant_outdoor_conditions_score_high(): void
    {
        $event = new Event(['is_outdoor' => true]);
        $weather = $this->weather(['temperature' => 22.0, 'wind_speed' => 2.0, 'rain' => 0]);

        $result = (new HealthScoreService)->scoreFor($event, $weather);

        $this->assertSame(100, $result['score']);
        $this->assertSame('excellent', $result['rating']);
    }

    public function test_harsh_conditions_lower_the_score(): void
    {
        $event = new Event(['is_outdoor' => true]);
        $weather = $this->weather(['temperature' => 40.0, 'wind_speed' => 18.0, 'rain' => 6.0], aqi: 5);

        $result = (new HealthScoreService)->scoreFor($event, $weather);

        $this->assertLessThan(50, $result['score']);
    }

    public function test_indoor_events_are_less_sensitive_to_weather(): void
    {
        $weather = $this->weather(['temperature' => 40.0, 'wind_speed' => 18.0, 'rain' => 6.0], aqi: 5);

        $outdoor = (new HealthScoreService)->scoreFor(new Event(['is_outdoor' => true]), $weather);
        $indoor = (new HealthScoreService)->scoreFor(new Event(['is_outdoor' => false]), $weather);

        $this->assertGreaterThan($outdoor['score'], $indoor['score']);
    }
}

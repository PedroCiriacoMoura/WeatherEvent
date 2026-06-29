<?php

namespace App\Services;

use App\Models\Event;

class HealthScoreService
{
    private const COMFORT_MIN = 16.0;

    private const COMFORT_MAX = 28.0;

    public function scoreFor(Event $event, array $weather): array
    {
        $conditions = $weather['forecast'][0] ?? $weather['current'] ?? [];
        $exposure = $event->is_outdoor ? 1.0 : 0.5;

        $temperature = $this->temperaturePenalty($conditions['temperature'] ?? null) * $exposure;
        $wind = $this->windPenalty($conditions['wind_speed'] ?? null) * $exposure;
        $precipitation = $this->precipitationPenalty($conditions) * $exposure;
        $airQuality = $this->airQualityPenalty($weather['air_quality']['aqi'] ?? null) * $exposure;

        $score = (int) round(max(0, min(100, 100 - ($temperature + $wind + $precipitation + $airQuality))));

        return [
            'score' => $score,
            'rating' => $this->rating($score),
            'is_outdoor' => $event->is_outdoor,
            'factors' => [
                'temperature' => round($temperature, 1),
                'wind' => round($wind, 1),
                'precipitation' => round($precipitation, 1),
                'air_quality' => round($airQuality, 1),
            ],
        ];
    }

    private function temperaturePenalty(?float $temperature): float
    {
        if ($temperature === null) {
            return 0.0;
        }

        if ($temperature < self::COMFORT_MIN) {
            return min(30.0, (self::COMFORT_MIN - $temperature) * 1.5);
        }

        if ($temperature > self::COMFORT_MAX) {
            return min(30.0, ($temperature - self::COMFORT_MAX) * 1.5);
        }

        return 0.0;
    }

    private function windPenalty(?float $speed): float
    {
        if ($speed === null) {
            return 0.0;
        }

        if ($speed <= 5.0) {
            return 0.0;
        }

        if ($speed <= 10.0) {
            return ($speed - 5.0) * 2.0;
        }

        return min(30.0, 10.0 + ($speed - 10.0) * 3.0);
    }

    private function precipitationPenalty(array $conditions): float
    {
        $probability = $conditions['precipitation_probability'] ?? null;

        if ($probability !== null) {
            return min(30.0, (float) $probability * 30.0);
        }

        $rain = (float) ($conditions['rain'] ?? 0);
        $snow = (float) ($conditions['snow'] ?? 0);

        return min(30.0, ($rain + $snow) * 5.0);
    }

    private function airQualityPenalty(?int $aqi): float
    {
        return match ($aqi) {
            2 => 5.0,
            3 => 12.0,
            4 => 22.0,
            5 => 35.0,
            default => 0.0,
        };
    }

    private function rating(int $score): string
    {
        return match (true) {
            $score >= 85 => 'excellent',
            $score >= 70 => 'good',
            $score >= 50 => 'fair',
            $score >= 30 => 'poor',
            default => 'hazardous',
        };
    }
}

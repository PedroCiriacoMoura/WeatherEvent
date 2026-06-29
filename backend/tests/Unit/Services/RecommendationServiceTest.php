<?php

namespace Tests\Unit\Services;

use App\Models\Event;
use App\Models\User;
use App\Services\RecommendationService;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    public function test_it_warns_about_rain_and_heat_for_outdoor_event(): void
    {
        $event = new Event(['is_outdoor' => true]);
        $weather = [
            'current' => ['temperature' => 33.0, 'wind_speed' => 2.0, 'rain' => 4.0],
            'forecast' => [],
            'air_quality' => ['aqi' => 1],
        ];
        $score = ['rating' => 'fair'];

        $result = (new RecommendationService)->for($event, $weather, $score);

        $this->assertSame('en', $result['locale']);
        $this->assertNotEmpty($result['items']);
    }

    public function test_it_respects_user_preferred_language(): void
    {
        $event = new Event(['is_outdoor' => true]);
        $event->setRelation('user', new User(['preferred_language' => 'pt']));
        $weather = [
            'current' => ['temperature' => 22.0, 'wind_speed' => 1.0, 'rain' => 0],
            'forecast' => [],
            'air_quality' => ['aqi' => 1],
        ];
        $score = ['rating' => 'excellent'];

        $result = (new RecommendationService)->for($event, $weather, $score);

        $this->assertSame('pt', $result['locale']);
        $this->assertSame('As condições estão ótimas para este evento.', $result['summary']);
    }

    public function test_it_suggests_moving_indoors_when_hazardous(): void
    {
        $event = new Event(['is_outdoor' => true]);
        $weather = [
            'current' => ['temperature' => 5.0, 'wind_speed' => 20.0, 'rain' => 10.0],
            'forecast' => [],
            'air_quality' => ['aqi' => 5],
        ];
        $score = ['rating' => 'hazardous'];

        $result = (new RecommendationService)->for($event, $weather, $score);

        $this->assertContains('Consider moving the event indoors or rescheduling.', $result['items']);
    }
}

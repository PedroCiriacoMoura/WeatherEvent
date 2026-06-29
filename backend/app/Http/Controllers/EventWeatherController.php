<?php

namespace App\Http\Controllers;

use App\Http\Resources\HealthScoreResource;
use App\Http\Resources\RecommendationResource;
use App\Http\Resources\WeatherResource;
use App\Models\Event;
use App\Services\HealthScoreService;
use App\Services\RecommendationService;
use App\Services\WeatherService;
use OpenApi\Attributes as OA;

class EventWeatherController extends Controller
{
    public function __construct(
        private readonly WeatherService $weather,
        private readonly HealthScoreService $healthScore,
        private readonly RecommendationService $recommendations,
    ) {}

    #[OA\Get(
        path: '/api/events/{event}/weather',
        summary: 'Retorna a análise climática do evento',
        security: [['sanctum' => []]],
        tags: ['Weather'],
        parameters: [new OA\Parameter(name: 'event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dados climáticos',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Weather')]),
            ),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
            new OA\Response(response: 502, description: 'Falha no provedor de clima'),
        ],
    )]
    public function weather(Event $event): WeatherResource
    {
        $this->authorize('view', $event);

        return WeatherResource::make($this->weather->forEvent($event));
    }

    #[OA\Get(
        path: '/api/events/{event}/health-score',
        summary: 'Retorna o índice de adequação climática do evento',
        security: [['sanctum' => []]],
        tags: ['Weather'],
        parameters: [new OA\Parameter(name: 'event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Índice de saúde climática',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/HealthScore')]),
            ),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
            new OA\Response(response: 502, description: 'Falha no provedor de clima'),
        ],
    )]
    public function healthScore(Event $event): HealthScoreResource
    {
        $this->authorize('view', $event);

        $weather = $this->weather->forEvent($event);

        return HealthScoreResource::make($this->healthScore->scoreFor($event, $weather));
    }

    #[OA\Get(
        path: '/api/events/{event}/recommendations',
        summary: 'Retorna recomendações climáticas para o evento',
        security: [['sanctum' => []]],
        tags: ['Weather'],
        parameters: [new OA\Parameter(name: 'event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recomendações',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Recommendation')]),
            ),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
            new OA\Response(response: 502, description: 'Falha no provedor de clima'),
        ],
    )]
    public function recommendations(Event $event): RecommendationResource
    {
        $this->authorize('view', $event);

        $weather = $this->weather->forEvent($event);
        $score = $this->healthScore->scoreFor($event, $weather);

        return RecommendationResource::make($this->recommendations->for($event, $weather, $score));
    }
}

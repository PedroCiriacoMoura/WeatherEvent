<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardChartsResource;
use App\Http\Resources\DashboardStatsResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    #[OA\Get(
        path: '/api/dashboard/stats',
        summary: 'Retorna as estatísticas agregadas do painel do usuário',
        security: [['sanctum' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estatísticas do painel',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/DashboardStats')]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ],
    )]
    public function stats(Request $request): DashboardStatsResource
    {
        return DashboardStatsResource::make($this->dashboard->stats($request->user()));
    }

    #[OA\Get(
        path: '/api/dashboard/charts',
        summary: 'Retorna as séries de gráficos do painel (temperatura, chuva, umidade, risco)',
        security: [['sanctum' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Séries dos gráficos',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/DashboardCharts')]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ],
    )]
    public function charts(Request $request): DashboardChartsResource
    {
        return DashboardChartsResource::make($this->dashboard->charts($request->user()));
    }
}

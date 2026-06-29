<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DashboardCharts',
    properties: [
        new OA\Property(
            property: 'temperature',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'at', type: 'string', format: 'date', example: '2026-07-15'),
                new OA\Property(property: 'value', type: 'number', format: 'float', example: 22.5),
            ], type: 'object'),
        ),
        new OA\Property(property: 'rain', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'humidity', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(
            property: 'risk',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'at', type: 'string', format: 'date'),
                new OA\Property(property: 'excellent', type: 'integer'),
                new OA\Property(property: 'good', type: 'integer'),
                new OA\Property(property: 'fair', type: 'integer'),
                new OA\Property(property: 'poor', type: 'integer'),
                new OA\Property(property: 'hazardous', type: 'integer'),
            ], type: 'object'),
        ),
    ],
    type: 'object',
)]
class DashboardChartsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}

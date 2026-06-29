<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DashboardStats',
    properties: [
        new OA\Property(property: 'total_events', type: 'integer', example: 42),
        new OA\Property(property: 'upcoming_events', type: 'integer', example: 12),
        new OA\Property(property: 'critical_events', type: 'integer', example: 3),
        new OA\Property(property: 'average_temperature', type: 'number', format: 'float', nullable: true, example: 21.4),
        new OA\Property(property: 'total_events_delta', type: 'number', format: 'float', nullable: true, example: 12.5),
        new OA\Property(property: 'upcoming_events_delta', type: 'number', format: 'float', nullable: true, example: -8.0),
    ],
    type: 'object',
)]
class DashboardStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Weather',
    properties: [
        new OA\Property(property: 'location', type: 'object'),
        new OA\Property(property: 'current', type: 'object'),
        new OA\Property(property: 'forecast', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'air_quality', type: 'object'),
        new OA\Property(property: 'units', type: 'string', example: 'metric'),
        new OA\Property(property: 'retrieved_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
class WeatherResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}

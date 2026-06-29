<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'HealthScore',
    properties: [
        new OA\Property(property: 'score', type: 'integer', example: 82),
        new OA\Property(property: 'rating', type: 'string', example: 'good'),
        new OA\Property(property: 'is_outdoor', type: 'boolean', example: true),
        new OA\Property(property: 'factors', type: 'object'),
    ],
    type: 'object',
)]
class HealthScoreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}

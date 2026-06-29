<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Recommendation',
    properties: [
        new OA\Property(property: 'locale', type: 'string', example: 'en'),
        new OA\Property(property: 'rating', type: 'string', example: 'good'),
        new OA\Property(property: 'summary', type: 'string'),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'string')),
    ],
    type: 'object',
)]
class RecommendationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}

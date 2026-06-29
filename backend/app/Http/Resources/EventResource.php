<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Event',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Summer Music Festival'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'city', type: 'string', example: 'Lisbon'),
        new OA\Property(property: 'country', type: 'string', example: 'PT'),
        new OA\Property(
            property: 'coordinates',
            properties: [
                new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 38.7223),
                new OA\Property(property: 'longitude', type: 'number', format: 'float', example: -9.1393),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Lisbon'),
        new OA\Property(property: 'attendees', type: 'integer', example: 250),
        new OA\Property(property: 'is_outdoor', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
/**
 * @mixin Event
 */
class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'city' => $this->city,
            'country' => $this->country,
            'coordinates' => $this->coordinates ? [
                'latitude' => $this->coordinates->latitude,
                'longitude' => $this->coordinates->longitude,
            ] : null,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'timezone' => $this->timezone,
            'attendees' => $this->attendees,
            'is_outdoor' => $this->is_outdoor,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'slug' => $this->category->slug,
                'name' => $this->category->name,
            ]),
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->status->id,
                'slug' => $this->status->slug,
                'name' => $this->status->name,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

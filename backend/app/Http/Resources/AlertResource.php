<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Alert',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'message', type: 'string', example: 'Heavy rain expected during the event.'),
        new OA\Property(property: 'locale', type: 'string', nullable: true, example: 'en'),
        new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'type',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'slug', type: 'string', example: 'rain'),
                new OA\Property(property: 'name', type: 'string', example: 'Rain'),
            ],
        ),
        new OA\Property(
            property: 'event',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Summer Music Festival'),
            ],
        ),
    ],
    type: 'object',
)]
class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'locale' => $this->locale,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->type->id,
                'slug' => $this->type->slug,
                'name' => $this->type->name,
            ]),
            'event' => $this->whenLoaded('event', fn () => [
                'id' => $this->event->id,
                'name' => $this->event->name,
            ]),
        ];
    }
}

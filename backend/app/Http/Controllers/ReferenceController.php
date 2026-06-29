<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlertTypeResource;
use App\Http\Resources\EventCategoryResource;
use App\Http\Resources\EventStatusResource;
use App\Models\AlertType;
use App\Models\EventCategory;
use App\Models\EventStatus;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

/**
 * Read-only reference/enumeration data used to populate the frontend's
 * select inputs and filters. Returns only active rows ordered by sort_order.
 */
class ReferenceController extends Controller
{
    #[OA\Get(
        path: '/api/event-categories',
        summary: 'Lista as categorias de evento ativas',
        security: [['sanctum' => []]],
        tags: ['Reference'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categorias de evento',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EventCategory')),
                ]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ],
    )]
    public function categories(): AnonymousResourceCollection
    {
        return EventCategoryResource::collection(
            EventCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        );
    }

    #[OA\Get(
        path: '/api/event-statuses',
        summary: 'Lista os status de evento ativos',
        security: [['sanctum' => []]],
        tags: ['Reference'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status de evento',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EventStatus')),
                ]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ],
    )]
    public function statuses(): AnonymousResourceCollection
    {
        return EventStatusResource::collection(
            EventStatus::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        );
    }

    #[OA\Get(
        path: '/api/alert-types',
        summary: 'Lista os tipos de alerta ativos',
        security: [['sanctum' => []]],
        tags: ['Reference'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tipos de alerta',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AlertType')),
                ]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ],
    )]
    public function alertTypes(): AnonymousResourceCollection
    {
        return AlertTypeResource::collection(
            AlertType::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        );
    }
}

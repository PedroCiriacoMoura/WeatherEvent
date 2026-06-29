<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\IndexEventRequest;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class EventController extends Controller
{
    #[OA\Get(
        path: '/api/events',
        summary: 'Lista eventos com filtros, ordenação e paginação',
        security: [['sanctum' => []]],
        tags: ['Events'],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'city', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'country', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'is_outdoor', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'starts_after', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'starts_before', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', enum: IndexEventRequest::SORTABLE)),
            new OA\Parameter(name: 'direction', in: 'query', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de eventos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Event')),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 422, description: 'Parâmetros inválidos'),
        ],
    )]
    public function index(IndexEventRequest $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->with(['category', 'status'])
            ->filter($request->filters())
            ->sort($request->sortField(), $request->sortDirection())
            ->paginate($request->perPage())
            ->appends($request->query());

        return EventResource::collection($events);
    }

    #[OA\Post(
        path: '/api/events',
        summary: 'Cria um evento',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['category_id', 'status_id', 'name', 'city', 'country', 'latitude', 'longitude', 'starts_at'],
                properties: [
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'status_id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Summer Music Festival'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'city', type: 'string', example: 'Lisbon'),
                    new OA\Property(property: 'country', type: 'string', example: 'PT'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 38.7223),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: -9.1393),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Lisbon'),
                    new OA\Property(property: 'attendees', type: 'integer', example: 250),
                    new OA\Property(property: 'is_outdoor', type: 'boolean', example: true),
                ],
            ),
        ),
        tags: ['Events'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Evento criado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Event')]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ],
    )]
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create($request->toAttributes());

        return EventResource::make($event->load(['category', 'status']))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/events/{event}',
        summary: 'Exibe um evento',
        security: [['sanctum' => []]],
        tags: ['Events'],
        parameters: [new OA\Parameter(name: 'event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Evento',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Event')]),
            ),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
        ],
    )]
    public function show(Event $event): EventResource
    {
        $this->authorize('view', $event);

        return EventResource::make($event->load(['category', 'status', 'user']));
    }

    #[OA\Put(
        path: '/api/events/{event}',
        summary: 'Atualiza um evento',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: '#/components/schemas/Event')),
        tags: ['Events'],
        parameters: [new OA\Parameter(name: 'event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Evento atualizado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Event')]),
            ),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ],
    )]
    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        $event->update($request->toAttributes());

        return EventResource::make($event->load(['category', 'status']));
    }

    #[OA\Delete(
        path: '/api/events/{event}',
        summary: 'Remove um evento',
        security: [['sanctum' => []]],
        tags: ['Events'],
        parameters: [new OA\Parameter(name: 'event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Evento removido'),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
        ],
    )]
    public function destroy(Event $event): Response
    {
        $this->authorize('delete', $event);

        $event->delete();

        return response()->noContent();
    }
}

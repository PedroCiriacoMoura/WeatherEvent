<?php

namespace App\Http\Controllers;

use App\Http\Requests\Alert\IndexAlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

class AlertController extends Controller
{
    #[OA\Get(
        path: '/api/alerts',
        summary: 'Lista os alertas do usuário autenticado',
        security: [['sanctum' => []]],
        tags: ['Alerts'],
        parameters: [
            new OA\Parameter(name: 'unread', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'type_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de alertas',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Alert')),
                ]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ],
    )]
    public function index(IndexAlertRequest $request): AnonymousResourceCollection
    {
        $alerts = $request->user()->alerts()
            ->with(['type', 'event'])
            ->when($request->onlyUnread(), fn ($query) => $query->unread())
            ->when($request->typeId(), fn ($query, $typeId) => $query->where('alert_type_id', $typeId))
            ->latest()
            ->paginate($request->perPage())
            ->appends($request->query());

        return AlertResource::collection($alerts);
    }

    #[OA\Get(
        path: '/api/events/{event}/alerts',
        summary: 'Lista os alertas de um evento específico',
        security: [['sanctum' => []]],
        tags: ['Alerts'],
        parameters: [new OA\Parameter(name: 'event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Alertas do evento',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Alert')),
                ]),
            ),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
        ],
    )]
    public function forEvent(Request $request, Event $event): AnonymousResourceCollection
    {
        $this->authorize('view', $event);

        $alerts = $event->alerts()
            ->where('user_id', $request->user()->id)
            ->with('type')
            ->latest()
            ->get();

        return AlertResource::collection($alerts);
    }

    #[OA\Patch(
        path: '/api/alerts/{alert}/read',
        summary: 'Marca um alerta como lido',
        security: [['sanctum' => []]],
        tags: ['Alerts'],
        parameters: [new OA\Parameter(name: 'alert', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Alerta marcado como lido',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Alert')]),
            ),
            new OA\Response(response: 403, description: 'Não autorizado'),
            new OA\Response(response: 404, description: 'Não encontrado'),
        ],
    )]
    public function read(Alert $alert): AlertResource
    {
        $this->authorize('update', $alert);

        if ($alert->read_at === null) {
            $alert->update(['read_at' => Carbon::now()]);
        }

        return AlertResource::make($alert->load(['type', 'event']));
    }

    #[OA\Post(
        path: '/api/alerts/read-all',
        summary: 'Marca todos os alertas do usuário como lidos',
        security: [['sanctum' => []]],
        tags: ['Alerts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Quantidade de alertas atualizados',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'updated', type: 'integer', example: 5)]),
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ],
    )]
    public function readAll(Request $request): JsonResponse
    {
        $updated = $request->user()->alerts()
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return response()->json(['updated' => $updated]);
    }
}

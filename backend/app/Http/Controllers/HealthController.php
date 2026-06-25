<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api',
        summary: 'Health check da API',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API operacional',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'version', type: 'string', example: '1.0.0'),
                    ]
                )
            ),
        ]
    )]
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'version' => config('app.version', '1.0.0'),
        ]);
    }
}

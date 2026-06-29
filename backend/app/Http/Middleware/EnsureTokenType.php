<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenType
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $payload = JWTAuth::setRequest($request)->parseToken()->getPayload();

        if (AuthService::sessionIsRevoked((string) $payload->get('sid'))) {
            abort(Response::HTTP_UNAUTHORIZED, 'Sessão expirada.');
        }

        if ($payload->get('type') !== $type) {
            abort(Response::HTTP_FORBIDDEN, 'Token sem permissão para esta ação.');
        }

        return $next($request);
    }
}

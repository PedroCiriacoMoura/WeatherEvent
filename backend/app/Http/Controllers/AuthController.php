<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthToken',
    title: 'AuthToken',
    description: 'Resposta de autenticação com o usuário, o access token (curto) e o refresh token (longo).',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'access_token', type: 'string', example: '1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...'),
        new OA\Property(property: 'refresh_token', type: 'string', example: '2|zYxWvUtSrQpOnMlKjIhGfEdCbA...'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', description: 'Validade do access token em segundos.', example: 3600),
    ]
)]
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    #[OA\Post(
        path: '/api/register',
        summary: 'Registra um novo usuário e retorna um token de acesso',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Maria Silva'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@weatherevent.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'senha-secreta'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'senha-secreta'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuário registrado com sucesso',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthToken')
            ),
            new OA\Response(response: 422, description: 'Dados de validação inválidos'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->tokenResponse($result, 201);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Autentica o usuário e retorna um token de acesso',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@weatherevent.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'senha-secreta'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Autenticação realizada com sucesso',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthToken')
            ),
            new OA\Response(response: 422, description: 'Credenciais inválidas'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->tokenResponse($result, 200);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Revoga o token de acesso utilizado na requisição',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout realizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logout realizado com sucesso.'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    #[OA\Get(
        path: '/api/me',
        summary: 'Retorna o usuário autenticado',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário autenticado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    #[OA\Post(
        path: '/api/refresh',
        summary: 'Renova o par de tokens usando o refresh token (manter conectado)',
        description: 'Requer o refresh token no header Authorization. Revoga o par atual e emite um novo (rotação).',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Novo par de tokens emitido',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthToken')
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 403, description: 'Token sem permissão de refresh'),
        ]
    )]
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refresh($request->user());

        return $this->tokenResponse($result, 200);
    }

    /**
     * Monta a resposta padronizada de autenticação (usuário + par de tokens Bearer).
     *
     * @param  array{user: \App\Models\User, access_token: string, refresh_token: string, expires_in: int}  $result
     */
    private function tokenResponse(array $result, int $status): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($result['user']),
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'token_type' => 'Bearer',
            'expires_in' => $result['expires_in'],
        ], $status);
    }
}

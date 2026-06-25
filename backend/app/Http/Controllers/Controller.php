<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'WeatherEvent API',
    description: 'API RESTful para gerenciamento de eventos com análise climática inteligente.',
    contact: new OA\Contact(
        name: 'Equipe WeatherEvent',
        email: 'dev@weatherevent.com'
    ),
    license: new OA\License(name: 'MIT')
)]
#[OA\Server(
    url: '%L5_SWAGGER_CONST_HOST%',
    description: 'Servidor principal'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Token de autenticação Sanctum. Obtenha via POST /api/login.'
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}

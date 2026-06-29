<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($this->shouldReturnJson($request, $e)) {
                return response()->json(
                    ['message' => $e->getMessage() ?: 'This action is unauthorized.'],
                    JsonResponse::HTTP_FORBIDDEN,
                );
            }
        });

        $this->renderable(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if ($this->shouldReturnJson($request, $e)) {
                return response()->json(
                    ['message' => 'Resource not found.'],
                    JsonResponse::HTTP_NOT_FOUND,
                );
            }
        });
    }

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}

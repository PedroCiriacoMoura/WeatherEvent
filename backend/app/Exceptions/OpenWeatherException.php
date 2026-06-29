<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class OpenWeatherException extends RuntimeException
{
    public function __construct(
        string $message = 'Unable to retrieve weather data.',
        private readonly int $status = JsonResponse::HTTP_BAD_GATEWAY,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function requestFailed(int $upstreamStatus): self
    {
        return new self(
            'The weather provider responded with an error.',
            $upstreamStatus >= 500 ? JsonResponse::HTTP_BAD_GATEWAY : JsonResponse::HTTP_SERVICE_UNAVAILABLE,
        );
    }

    public static function unreachable(?Throwable $previous = null): self
    {
        return new self(
            'The weather provider is currently unreachable.',
            JsonResponse::HTTP_SERVICE_UNAVAILABLE,
            $previous,
        );
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->status);
    }
}

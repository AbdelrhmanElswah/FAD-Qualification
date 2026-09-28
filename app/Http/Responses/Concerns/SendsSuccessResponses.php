<?php

declare(strict_types=1);

namespace App\Http\Responses\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

trait SendsSuccessResponses
{
    /**
     * @param  array<string, mixed>  $additional
     */
    protected function okResponse(
        JsonResource|array|null $data = null,
        string $message = 'Request completed successfully.',
        array $additional = [],
    ): JsonResponse {
        return $this->successResponse($data, $message, Response::HTTP_OK, $additional);
    }

    /**
     * @param  array<string, mixed>  $additional
     */
    protected function createdResponse(
        JsonResource|array|null $data = null,
        string $message = 'Resource created successfully.',
        array $additional = [],
    ): JsonResponse {
        return $this->successResponse($data, $message, Response::HTTP_CREATED, $additional);
    }

    /**
     * @param  array<string, mixed>  $additional
     */
    protected function successResponse(
        JsonResource|array|null $data = null,
        string $message = 'Request completed successfully.',
        int $status = Response::HTTP_OK,
        array $additional = [],
    ): JsonResponse {
        $payload = ['success' => true, 'message' => $message];

        if ($data instanceof JsonResource) {
            $payload += $this->flattenResource($data);
        } elseif ($data !== null) {
            $payload['data'] = $data;
        }

        return new JsonResponse($payload + $additional, $status);
    }

    /**
     * @return array<string, mixed>
     */
    private function flattenResource(JsonResource $resource): array
    {
        /** @var array<string, mixed> $resolved */
        $resolved = $resource->response()->getData(true);

        $payload = ['data' => $resolved['data'] ?? $resolved];

        foreach (['links', 'meta'] as $key) {
            if (isset($resolved[$key])) {
                $payload[$key] = $resolved[$key];
            }
        }

        return $payload;
    }
}

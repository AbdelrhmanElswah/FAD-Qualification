<?php

declare(strict_types=1);

namespace App\Http\Responses\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds the single success envelope used by every endpoint:
 *
 *     { "success": true, "message": "...", "data": ..., "meta": {...} }
 */
trait SendsSuccessResponses
{
    /**
     * Return a 200 response.
     *
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
     * Return a 201 response for a freshly created resource.
     *
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
     * Return a 204 response with no body.
     */
    protected function noContentResponse(): JsonResponse
    {
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    /**
     * Return an arbitrary success response.
     *
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
     * Unwrap a resource so its `data`, `links` and `meta` keys sit beside the envelope
     * instead of being nested inside another `data` key.
     *
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

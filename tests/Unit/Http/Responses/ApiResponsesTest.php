<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Responses;

use App\Http\Resources\TaskResource;
use App\Http\Responses\Concerns\InteractsWithApiResponses;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiResponsesTest extends TestCase
{
    private object $responder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responder = new class
        {
            use InteractsWithApiResponses;

            public function ok(JsonResource|array|null $data = null, string $message = 'Done.'): JsonResponse
            {
                return $this->okResponse($data, $message);
            }

            public function created(JsonResource|array|null $data = null): JsonResponse
            {
                return $this->createdResponse($data, 'Created.');
            }

            /**
             * @param  array<string, list<string>>  $errors
             */
            public function error(string $message, int $status, array $errors = []): JsonResponse
            {
                return $this->errorResponse($message, $status, $errors);
            }

            /**
             * @param  array<string, list<string>>  $errors
             */
            public function validationError(array $errors): JsonResponse
            {
                return $this->validationErrorResponse($errors);
            }

            public function notFound(): JsonResponse
            {
                return $this->notFoundResponse('Nothing here.');
            }

            public function serverError(): JsonResponse
            {
                return $this->serverErrorResponse();
            }
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(JsonResponse $response): array
    {
        return json_decode((string) $response->getContent(), true);
    }

    #[Test]
    public function it_wraps_array_data_in_the_success_envelope(): void
    {
        $response = $this->responder->ok(['id' => 1], 'Task retrieved successfully.');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'Task retrieved successfully.',
            'data' => ['id' => 1],
        ], $this->decode($response));
    }

    #[Test]
    public function it_omits_the_data_key_when_there_is_nothing_to_return(): void
    {
        $response = $this->responder->ok(null, 'Task deleted successfully.');

        $this->assertSame([
            'success' => true,
            'message' => 'Task deleted successfully.',
        ], $this->decode($response));
    }

    #[Test]
    public function it_reports_a_created_resource_with_a_201(): void
    {
        $response = $this->responder->created(['id' => 9]);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($this->decode($response)['success']);
    }

    #[Test]
    public function it_lifts_a_single_resource_out_of_its_data_wrapper(): void
    {
        $task = Task::factory()->make(['title' => 'Unsaved task']);
        $task->id = 1;

        $response = $this->responder->ok(TaskResource::make($task));
        $payload = $this->decode($response);

        $this->assertTrue($payload['success']);
        $this->assertSame('Unsaved task', $payload['data']['title']);
        $this->assertArrayNotHasKey('data', $payload['data']);
    }

    #[Test]
    public function it_places_pagination_links_and_meta_beside_the_data(): void
    {
        $tasks = Task::factory()->count(2)->make();
        $paginator = new LengthAwarePaginator($tasks, 2, 15, 1, ['path' => 'http://localhost/api/v1/tasks']);

        $response = $this->responder->ok(TaskResource::collection($paginator));
        $payload = $this->decode($response);

        $this->assertCount(2, $payload['data']);
        $this->assertArrayHasKey('links', $payload);
        $this->assertSame(15, $payload['meta']['per_page']);
        $this->assertSame(2, $payload['meta']['total']);
    }

    #[Test]
    public function it_wraps_failures_in_the_error_envelope(): void
    {
        $response = $this->responder->error('Bad request.', 400);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Bad request.',
        ], $this->decode($response));
    }

    #[Test]
    public function it_includes_field_errors_when_there_are_any(): void
    {
        $response = $this->responder->validationError(['title' => ['A task title is required.']]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => ['title' => ['A task title is required.']],
        ], $this->decode($response));
    }

    #[Test]
    public function it_omits_the_errors_key_when_there_is_no_field_detail(): void
    {
        $this->assertArrayNotHasKey('errors', $this->decode($this->responder->notFound()));
        $this->assertArrayNotHasKey('errors', $this->decode($this->responder->serverError()));
    }

    #[Test]
    public function it_uses_the_expected_status_codes_for_each_failure(): void
    {
        $this->assertSame(404, $this->responder->notFound()->getStatusCode());
        $this->assertSame(500, $this->responder->serverError()->getStatusCode());
    }
}

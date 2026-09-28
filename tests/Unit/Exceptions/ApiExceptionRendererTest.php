<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\ApiException;
use App\Exceptions\ApiExceptionRenderer;
use App\Exceptions\TaskNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;
use Throwable;

final class ApiExceptionRendererTest extends TestCase
{
    private ApiExceptionRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new ApiExceptionRenderer;
    }

    private function render(Throwable $e, string $uri = '/api/v1/tasks'): ?JsonResponse
    {
        return ($this->renderer)($e, Request::create($uri));
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(JsonResponse $response): array
    {
        return json_decode((string) $response->getContent(), true);
    }

    #[Test]
    public function it_leaves_non_api_requests_to_the_default_handler(): void
    {
        $this->assertNull($this->render(new NotFoundHttpException, '/tasks'));
    }

    #[Test]
    public function it_handles_requests_that_explicitly_ask_for_json(): void
    {
        $request = Request::create('/tasks');
        $request->headers->set('Accept', 'application/json');

        $this->assertInstanceOf(JsonResponse::class, ($this->renderer)(new NotFoundHttpException, $request));
    }

    #[Test]
    public function it_renders_a_domain_exception_with_its_own_status(): void
    {
        $response = $this->render(TaskNotFoundException::withId(42));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Task [42] was not found.',
        ], $this->decode($response));
    }

    #[Test]
    public function it_passes_through_field_errors_from_a_domain_exception(): void
    {
        $exception = new class('Cannot complete this task.', 409, ['is_completed' => ['Already completed.']]) extends ApiException {};

        $response = $this->render($exception);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(['is_completed' => ['Already completed.']], $this->decode($response)['errors']);
    }

    #[Test]
    public function it_renders_validation_failures_as_422(): void
    {
        $exception = ValidationException::withMessages([
            'title' => ['A task title is required.'],
        ]);

        $response = $this->render($exception);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('The given data was invalid.', $this->decode($response)['message']);
        $this->assertSame(['title' => ['A task title is required.']], $this->decode($response)['errors']);
    }

    #[Test]
    public function it_renders_an_unknown_endpoint_as_404(): void
    {
        $response = $this->render(new NotFoundHttpException);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('The requested endpoint does not exist.', $this->decode($response)['message']);
    }

    #[Test]
    public function it_renders_a_disallowed_method_as_405(): void
    {
        $response = $this->render(new MethodNotAllowedHttpException(['GET']));

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame(
            'This method is not allowed for the requested endpoint.',
            $this->decode($response)['message'],
        );
    }

    #[Test]
    public function it_renders_missing_authentication_as_401(): void
    {
        $response = $this->render(new AuthenticationException);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthenticated.', $this->decode($response)['message']);
    }

    #[Test]
    public function it_honours_the_status_of_any_other_http_exception(): void
    {
        $response = $this->render(new AccessDeniedHttpException('Nope.'));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Nope.', $this->decode($response)['message']);
    }

    #[Test]
    public function it_hides_unexpected_failures_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        $response = $this->render(new RuntimeException('Database credentials rejected'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(
            'An unexpected error occurred. Please try again later.',
            $this->decode($response)['message'],
        );
    }

    #[Test]
    public function it_reveals_unexpected_failures_when_debug_is_on(): void
    {
        config(['app.debug' => true]);

        $response = $this->render(new RuntimeException('Database credentials rejected'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Database credentials rejected', $this->decode($response)['message']);
    }
}

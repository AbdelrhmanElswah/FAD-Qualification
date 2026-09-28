<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DataTransferObjects\TaskData;
use App\DataTransferObjects\TaskFilters;
use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface&MockInterface $repository;

    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TaskRepositoryInterface::class);
        $this->service = new TaskService($this->repository);
    }

    #[Test]
    public function it_delegates_listing_to_the_repository(): void
    {
        $filters = new TaskFilters(isCompleted: true);
        $paginator = new LengthAwarePaginator([], 0, 15);

        $this->repository->shouldReceive('paginate')
            ->once()
            ->with($filters, 15)
            ->andReturn($paginator);

        $this->assertSame($paginator, $this->service->list($filters, 15));
    }

    #[Test]
    public function it_returns_the_task_it_finds(): void
    {
        $task = new Task(['title' => 'Existing task']);

        $this->repository->shouldReceive('findById')->once()->with(7)->andReturn($task);

        $this->assertSame($task, $this->service->find(7));
    }

    #[Test]
    public function it_throws_when_the_task_does_not_exist(): void
    {
        $this->repository->shouldReceive('findById')->once()->with(7)->andReturnNull();

        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task [7] was not found.');

        $this->service->find(7);
    }

    #[Test]
    public function it_reports_a_missing_task_as_a_not_found_failure(): void
    {
        $this->repository->shouldReceive('findById')->andReturnNull();

        try {
            $this->service->find(7);
            $this->fail('Expected a TaskNotFoundException.');
        } catch (TaskNotFoundException $e) {
            $this->assertSame(404, $e->status());
            $this->assertSame([], $e->errors());
        }
    }

    #[Test]
    public function it_creates_a_task_from_the_provided_attributes_only(): void
    {
        $created = new Task(['title' => 'New task']);

        $this->repository->shouldReceive('create')
            ->once()
            ->with(['title' => 'New task'])
            ->andReturn($created);

        $result = $this->service->create(TaskData::fromValidated(['title' => 'New task']));

        $this->assertSame($created, $result);
    }

    #[Test]
    public function it_looks_the_task_up_before_updating_it(): void
    {
        $existing = new Task(['title' => 'Before']);
        $updated = new Task(['title' => 'Before']);

        $this->repository->shouldReceive('findById')->once()->with(3)->andReturn($existing);
        $this->repository->shouldReceive('update')
            ->once()
            ->with($existing, ['is_completed' => true])
            ->andReturn($updated);

        $result = $this->service->update(3, TaskData::fromValidated(['is_completed' => true]));

        $this->assertSame($updated, $result);
    }

    #[Test]
    public function it_does_not_update_a_missing_task(): void
    {
        $this->repository->shouldReceive('findById')->once()->with(3)->andReturnNull();
        $this->repository->shouldNotReceive('update');

        $this->expectException(TaskNotFoundException::class);

        $this->service->update(3, TaskData::fromValidated(['title' => 'Never written']));
    }

    #[Test]
    public function it_deletes_the_task_it_finds(): void
    {
        $task = new Task(['title' => 'Doomed']);

        $this->repository->shouldReceive('findById')->once()->with(4)->andReturn($task);
        $this->repository->shouldReceive('delete')->once()->with($task)->andReturnTrue();

        $this->service->delete(4);
    }

    #[Test]
    public function it_does_not_delete_a_missing_task(): void
    {
        $this->repository->shouldReceive('findById')->once()->with(4)->andReturnNull();
        $this->repository->shouldNotReceive('delete');

        $this->expectException(TaskNotFoundException::class);

        $this->service->delete(4);
    }
}

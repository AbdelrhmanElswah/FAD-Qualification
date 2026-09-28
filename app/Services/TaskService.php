<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\TaskData;
use App\DataTransferObjects\TaskFilters;
use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Application service for the task use cases.
 *
 * Controllers talk to this class only; it owns the business rules and delegates
 * all persistence to the repository behind its interface.
 */
final readonly class TaskService
{
    public function __construct(private TaskRepositoryInterface $tasks) {}

    /**
     * List tasks for the given filters.
     *
     * @return LengthAwarePaginator<int, Task>
     */
    public function list(TaskFilters $filters, int $perPage): LengthAwarePaginator
    {
        return $this->tasks->paginate($filters, $perPage);
    }

    /**
     * Fetch a single task.
     *
     * @throws TaskNotFoundException
     */
    public function find(int $id): Task
    {
        return $this->tasks->findById($id) ?? throw TaskNotFoundException::withId($id);
    }

    /**
     * Create a task from the validated payload.
     */
    public function create(TaskData $data): Task
    {
        return $this->tasks->create($data->toArray());
    }

    /**
     * Apply the payload to an existing task.
     *
     * @throws TaskNotFoundException
     */
    public function update(int $id, TaskData $data): Task
    {
        return $this->tasks->update($this->find($id), $data->toArray());
    }

    /**
     * Permanently remove a task.
     *
     * @throws TaskNotFoundException
     */
    public function delete(int $id): void
    {
        $this->tasks->delete($this->find($id));
    }
}

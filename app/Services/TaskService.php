<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\TaskData;
use App\DataTransferObjects\TaskFilters;
use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class TaskService
{
    public function __construct(private TaskRepositoryInterface $tasks) {}

    /**
     * @return LengthAwarePaginator<int, Task>
     */
    public function list(TaskFilters $filters, int $perPage): LengthAwarePaginator
    {
        return $this->tasks->paginate($filters, $perPage);
    }

    /**
     * @throws TaskNotFoundException
     */
    public function find(int $id): Task
    {
        return $this->tasks->findById($id) ?? throw TaskNotFoundException::withId($id);
    }

    public function create(TaskData $data): Task
    {
        return $this->tasks->create($data->toArray());
    }

    /**
     * @throws TaskNotFoundException
     */
    public function update(int $id, TaskData $data): Task
    {
        return $this->tasks->update($this->find($id), $data->toArray());
    }

    /**
     * @throws TaskNotFoundException
     */
    public function delete(int $id): void
    {
        $this->tasks->delete($this->find($id));
    }
}

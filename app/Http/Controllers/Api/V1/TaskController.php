<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\IndexTaskRequest;
use App\Http\Requests\Api\V1\Task\StoreTaskRequest;
use App\Http\Requests\Api\V1\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Responses\Concerns\InteractsWithApiResponses;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;

final class TaskController extends Controller
{
    use InteractsWithApiResponses;

    public function __construct(private readonly TaskService $tasks) {}

    public function index(IndexTaskRequest $request): JsonResponse
    {
        $tasks = $this->tasks->list($request->filters(), $request->perPage());

        return $this->okResponse(
            TaskResource::collection($tasks),
            'Tasks retrieved successfully.',
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->tasks->create($request->toData());

        return $this->createdResponse(
            TaskResource::make($task),
            'Task created successfully.',
        );
    }

    public function show(int $id): JsonResponse
    {
        return $this->okResponse(
            TaskResource::make($this->tasks->find($id)),
            'Task retrieved successfully.',
        );
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $task = $this->tasks->update($id, $request->toData());

        return $this->okResponse(
            TaskResource::make($task),
            'Task updated successfully.',
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->tasks->delete($id);

        return $this->okResponse(message: 'Task deleted successfully.');
    }
}

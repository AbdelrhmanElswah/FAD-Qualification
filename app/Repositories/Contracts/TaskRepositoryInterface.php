<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DataTransferObjects\TaskFilters;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Task-specific persistence operations.
 *
 * @extends RepositoryInterface<Task>
 */
interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * Retrieve a filtered, sorted page of tasks.
     *
     * @return LengthAwarePaginator<int, Task>
     */
    public function paginate(TaskFilters $filters, int $perPage): LengthAwarePaginator;
}

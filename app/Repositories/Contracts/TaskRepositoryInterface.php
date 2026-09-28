<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DataTransferObjects\TaskFilters;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Task>
 */
interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Task>
     */
    public function paginate(TaskFilters $filters, int $perPage): LengthAwarePaginator;
}

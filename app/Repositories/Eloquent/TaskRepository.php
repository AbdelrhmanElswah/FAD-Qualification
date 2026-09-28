<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\TaskFilters;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<Task>
 */
final class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function __construct(Task $task)
    {
        parent::__construct($task);
    }

    /**
     * @return LengthAwarePaginator<int, Task>
     */
    public function paginate(TaskFilters $filters, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->when(
                $filters->isCompleted !== null,
                fn (Builder $query) => $query->where('is_completed', $filters->isCompleted),
            )
            ->when(
                $filters->search !== null,
                fn (Builder $query) => $query->where(
                    fn (Builder $nested) => $this->applySearch($nested, (string) $filters->search),
                ),
            )
            ->orderBy($filters->sortBy, $filters->sortDirection)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  Builder<Task>  $query
     */
    private function applySearch(Builder $query, string $term): void
    {
        foreach (Task::SEARCHABLE as $column) {
            $query->orWhere($column, 'like', "%{$term}%");
        }
    }
}

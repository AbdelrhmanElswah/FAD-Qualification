<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 *
 * @implements RepositoryInterface<TModel>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @param  TModel  $model
     */
    public function __construct(protected readonly Model $model) {}

    /**
     * @return Collection<int, TModel>
     */
    public function all(): Collection
    {
        return $this->query()->get();
    }

    /**
     * @return TModel|null
     */
    public function findById(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function update(Model $model, array $attributes): Model
    {
        if ($attributes !== []) {
            $model->update($attributes);
        }

        return $model->refresh();
    }

    /**
     * @param  TModel  $model
     */
    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * @return Builder<TModel>
     */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }
}

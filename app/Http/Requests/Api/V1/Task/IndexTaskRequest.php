<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Task;

use App\DataTransferObjects\TaskFilters;
use App\Http\Requests\Api\V1\ApiRequest;
use App\Models\Task;
use Illuminate\Validation\Rule;

final class IndexTaskRequest extends ApiRequest
{
    private const int DEFAULT_PER_PAGE = 15;

    private const int MAX_PER_PAGE = 100;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_completed' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', Rule::in(Task::SORTABLE)],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sort_by.in' => 'Sorting is only supported on: '.implode(', ', Task::SORTABLE).'.',
            'sort_direction.in' => 'The sort direction must be either asc or desc.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeBooleans(['is_completed']);
    }

    public function filters(): TaskFilters
    {
        return TaskFilters::fromValidated($this->validated());
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', self::DEFAULT_PER_PAGE);
    }
}

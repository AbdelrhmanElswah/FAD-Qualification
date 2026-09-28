<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Task;

use App\DataTransferObjects\TaskData;
use App\Http\Requests\Api\V1\ApiRequest;

final class StoreTaskRequest extends ApiRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_completed' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A task title is required.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeBooleans(['is_completed']);
    }

    /**
     * The validated payload as a domain object.
     */
    public function toData(): TaskData
    {
        return TaskData::fromValidated($this->validated());
    }
}

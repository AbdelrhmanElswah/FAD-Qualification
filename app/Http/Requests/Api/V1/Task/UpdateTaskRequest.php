<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Task;

use App\DataTransferObjects\TaskData;
use App\Http\Requests\Api\V1\ApiRequest;

final class UpdateTaskRequest extends ApiRequest
{
    /**
     * PUT expects the client to send the title it wants the task to end up with;
     * PATCH accepts any subset of the fields.
     *
     * Either way, only the attributes actually present in the payload are written,
     * so an omitted field keeps its stored value.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $titlePresence = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'title' => [$titlePresence, 'string', 'min:3', 'max:255'],
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

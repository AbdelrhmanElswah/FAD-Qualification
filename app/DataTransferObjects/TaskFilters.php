<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class TaskFilters
{
    public function __construct(
        public ?bool $isCompleted = null,
        public ?string $search = null,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc',
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $search = isset($validated['search']) ? trim((string) $validated['search']) : '';

        return new self(
            isCompleted: isset($validated['is_completed'])
                ? filter_var($validated['is_completed'], FILTER_VALIDATE_BOOLEAN)
                : null,
            search: $search !== '' ? $search : null,
            sortBy: (string) ($validated['sort_by'] ?? 'created_at'),
            sortDirection: (string) ($validated['sort_direction'] ?? 'desc'),
        );
    }
}

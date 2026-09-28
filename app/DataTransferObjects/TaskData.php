<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class TaskData implements Arrayable
{
    /**
     * @param  list<string>  $provided
     */
    private function __construct(
        public ?string $title,
        public ?string $description,
        public ?bool $isCompleted,
        private array $provided,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            title: isset($validated['title']) ? (string) $validated['title'] : null,
            description: isset($validated['description']) ? (string) $validated['description'] : null,
            isCompleted: isset($validated['is_completed']) ? (bool) $validated['is_completed'] : null,
            provided: array_keys($validated),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $attributes = [
            'title' => $this->title,
            'description' => $this->description,
            'is_completed' => $this->isCompleted,
        ];

        return array_filter(
            $attributes,
            fn (string $column): bool => in_array($column, $this->provided, true),
            ARRAY_FILTER_USE_KEY,
        );
    }
}

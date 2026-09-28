<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects;

use App\DataTransferObjects\TaskFilters;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TaskFiltersTest extends TestCase
{
    #[Test]
    public function it_falls_back_to_newest_first_with_no_filters(): void
    {
        $filters = TaskFilters::fromValidated([]);

        $this->assertNull($filters->isCompleted);
        $this->assertNull($filters->search);
        $this->assertSame('created_at', $filters->sortBy);
        $this->assertSame('desc', $filters->sortDirection);
    }

    #[Test]
    #[DataProvider('completionValues')]
    public function it_parses_the_completion_filter(mixed $input, bool $expected): void
    {
        $filters = TaskFilters::fromValidated(['is_completed' => $input]);

        $this->assertSame($expected, $filters->isCompleted);
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function completionValues(): array
    {
        return [
            'boolean true' => [true, true],
            'boolean false' => [false, false],
            'integer one' => [1, true],
            'integer zero' => [0, false],
            'string true' => ['true', true],
            'string false' => ['false', false],
        ];
    }

    #[Test]
    public function it_trims_the_search_term(): void
    {
        $filters = TaskFilters::fromValidated(['search' => '  runbook  ']);

        $this->assertSame('runbook', $filters->search);
    }

    #[Test]
    public function it_discards_a_blank_search_term(): void
    {
        $this->assertNull(TaskFilters::fromValidated(['search' => '   '])->search);
        $this->assertNull(TaskFilters::fromValidated(['search' => ''])->search);
        $this->assertNull(TaskFilters::fromValidated(['search' => null])->search);
    }

    #[Test]
    public function it_keeps_the_requested_sorting(): void
    {
        $filters = TaskFilters::fromValidated([
            'sort_by' => 'title',
            'sort_direction' => 'asc',
        ]);

        $this->assertSame('title', $filters->sortBy);
        $this->assertSame('asc', $filters->sortDirection);
    }
}

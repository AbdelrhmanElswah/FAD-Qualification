<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Task;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TaskTest extends TestCase
{
    #[Test]
    public function it_defaults_to_incomplete(): void
    {
        $this->assertFalse((new Task)->is_completed);
    }

    #[Test]
    public function it_casts_the_completion_flag_to_a_boolean(): void
    {
        $task = new Task(['is_completed' => 1]);

        $this->assertTrue($task->is_completed);
    }

    #[Test]
    public function it_allows_mass_assignment_of_the_editable_columns(): void
    {
        $task = new Task([
            'title' => 'Mass assigned',
            'description' => 'Also assigned',
            'is_completed' => true,
        ]);

        $this->assertSame('Mass assigned', $task->title);
        $this->assertSame('Also assigned', $task->description);
        $this->assertTrue($task->is_completed);
    }

    #[Test]
    public function it_ignores_mass_assignment_of_the_primary_key(): void
    {
        $task = new Task(['id' => 99, 'title' => 'Guarded']);

        $this->assertNull($task->id);
    }

    #[Test]
    public function it_allow_lists_the_searchable_columns(): void
    {
        $this->assertSame(['title', 'description'], Task::SEARCHABLE);
    }

    #[Test]
    public function it_allow_lists_the_sortable_columns(): void
    {
        $this->assertSame(
            ['id', 'title', 'is_completed', 'created_at', 'updated_at'],
            Task::SORTABLE,
        );
    }
}

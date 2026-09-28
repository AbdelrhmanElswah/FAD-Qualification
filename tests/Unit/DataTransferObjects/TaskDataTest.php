<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects;

use App\DataTransferObjects\TaskData;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TaskDataTest extends TestCase
{
    #[Test]
    public function it_only_exposes_attributes_that_were_provided(): void
    {
        $data = TaskData::fromValidated(['title' => 'Only the title']);

        $this->assertSame(['title' => 'Only the title'], $data->toArray());
    }

    #[Test]
    public function it_keeps_an_explicit_null_description(): void
    {
        $data = TaskData::fromValidated(['description' => null]);

        $this->assertSame(['description' => null], $data->toArray());
    }

    #[Test]
    public function it_casts_the_completion_flag_to_a_boolean(): void
    {
        $data = TaskData::fromValidated(['is_completed' => 1]);

        $this->assertTrue($data->isCompleted);
        $this->assertSame(['is_completed' => true], $data->toArray());
    }

    #[Test]
    public function it_returns_an_empty_array_for_an_empty_payload(): void
    {
        $this->assertSame([], TaskData::fromValidated([])->toArray());
    }
}

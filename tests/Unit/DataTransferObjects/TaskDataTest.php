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
    public function it_exposes_every_attribute_when_all_are_provided(): void
    {
        $data = TaskData::fromValidated([
            'title' => 'Full payload',
            'description' => 'With a description',
            'is_completed' => true,
        ]);

        $this->assertSame([
            'title' => 'Full payload',
            'description' => 'With a description',
            'is_completed' => true,
        ], $data->toArray());
    }

    #[Test]
    public function it_keeps_an_explicit_null_description(): void
    {
        $data = TaskData::fromValidated(['description' => null]);

        $this->assertSame(['description' => null], $data->toArray());
        $this->assertNull($data->description);
    }

    #[Test]
    public function it_omits_a_description_that_was_never_sent(): void
    {
        $data = TaskData::fromValidated(['title' => 'No description key']);

        $this->assertArrayNotHasKey('description', $data->toArray());
    }

    #[Test]
    public function it_casts_the_completion_flag_to_a_boolean(): void
    {
        $data = TaskData::fromValidated(['is_completed' => 1]);

        $this->assertTrue($data->isCompleted);
        $this->assertSame(['is_completed' => true], $data->toArray());
    }

    #[Test]
    public function it_treats_a_falsy_completion_flag_as_provided(): void
    {
        $data = TaskData::fromValidated(['is_completed' => false]);

        $this->assertSame(['is_completed' => false], $data->toArray());
    }

    #[Test]
    public function it_casts_the_title_to_a_string(): void
    {
        $data = TaskData::fromValidated(['title' => 12345]);

        $this->assertSame('12345', $data->title);
    }

    #[Test]
    public function it_returns_an_empty_array_for_an_empty_payload(): void
    {
        $data = TaskData::fromValidated([]);

        $this->assertSame([], $data->toArray());
        $this->assertNull($data->title);
        $this->assertNull($data->description);
        $this->assertNull($data->isCompleted);
    }

    #[Test]
    public function it_ignores_keys_it_does_not_recognise(): void
    {
        $data = TaskData::fromValidated(['title' => 'Kept', 'unexpected' => 'dropped']);

        $this->assertSame(['title' => 'Kept'], $data->toArray());
    }
}

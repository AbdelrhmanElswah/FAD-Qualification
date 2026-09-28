<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/v1/tasks';

    // ---------------------------------------------------------------- index

    #[Test]
    public function it_lists_tasks_in_the_standard_envelope(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tasks retrieved successfully.')
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [['id', 'title', 'description', 'is_completed', 'created_at', 'updated_at']],
                'links',
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    #[Test]
    public function it_filters_tasks_by_completion_state(): void
    {
        Task::factory()->count(2)->completed()->create();
        Task::factory()->count(3)->pending()->create();

        $this->getJson(self::ENDPOINT.'?is_completed=true')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson(self::ENDPOINT.'?is_completed=false')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_searches_tasks_by_title_and_description(): void
    {
        Task::factory()->create(['title' => 'Write the deployment runbook']);
        Task::factory()->create(['title' => 'Unrelated', 'description' => 'Mentions runbook in the body']);
        Task::factory()->create(['title' => 'Something else', 'description' => 'Nothing relevant']);

        $this->getJson(self::ENDPOINT.'?search=runbook')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_sorts_and_paginates_the_listing(): void
    {
        Task::factory()->create(['title' => 'Beta']);
        Task::factory()->create(['title' => 'Alpha']);
        Task::factory()->create(['title' => 'Gamma']);

        $response = $this->getJson(self::ENDPOINT.'?sort_by=title&sort_direction=asc&per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Alpha')
            ->assertJsonPath('data.1.title', 'Beta')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);
    }

    #[Test]
    public function it_rejects_sorting_by_an_unknown_column(): void
    {
        $this->getJson(self::ENDPOINT.'?sort_by=password')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('sort_by');
    }

    // ----------------------------------------------------------------- show

    #[Test]
    public function it_shows_a_single_task(): void
    {
        $task = Task::factory()->create(['title' => 'Review the pull request']);

        $this->getJson(self::ENDPOINT.'/'.$task->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Task retrieved successfully.')
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.title', 'Review the pull request');
    }

    #[Test]
    public function it_returns_not_found_for_a_missing_task(): void
    {
        $this->getJson(self::ENDPOINT.'/999')
            ->assertNotFound()
            ->assertExactJson([
                'success' => false,
                'message' => 'Task [999] was not found.',
            ]);
    }

    // ---------------------------------------------------------------- store

    #[Test]
    public function it_creates_a_task(): void
    {
        $payload = [
            'title' => 'Ship the tasks API',
            'description' => 'Repository, service and resource layers.',
        ];

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Task created successfully.')
            ->assertJsonPath('data.title', 'Ship the tasks API')
            ->assertJsonPath('data.is_completed', false);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Ship the tasks API',
            'description' => 'Repository, service and resource layers.',
            'is_completed' => false,
        ]);
    }

    #[Test]
    public function it_creates_a_task_that_is_already_completed(): void
    {
        $this->postJson(self::ENDPOINT, ['title' => 'Already done', 'is_completed' => true])
            ->assertCreated()
            ->assertJsonPath('data.is_completed', true);
    }

    #[Test]
    public function it_requires_a_title_when_creating_a_task(): void
    {
        $this->postJson(self::ENDPOINT, [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The given data was invalid.')
            ->assertJsonPath('errors.title.0', 'A task title is required.');

        $this->assertDatabaseCount('tasks', 0);
    }

    #[Test]
    public function it_validates_the_shape_of_the_create_payload(): void
    {
        $this->postJson(self::ENDPOINT, [
            'title' => 'ab',
            'description' => str_repeat('x', 5001),
            'is_completed' => 'not-a-boolean',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description', 'is_completed']);
    }

    // --------------------------------------------------------------- update

    #[Test]
    public function it_replaces_a_task_with_put(): void
    {
        $task = Task::factory()->pending()->create(['title' => 'Old title']);

        $this->putJson(self::ENDPOINT.'/'.$task->id, [
            'title' => 'New title',
            'description' => 'Rewritten.',
            'is_completed' => true,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Task updated successfully.')
            ->assertJsonPath('data.title', 'New title')
            ->assertJsonPath('data.description', 'Rewritten.')
            ->assertJsonPath('data.is_completed', true);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New title',
            'is_completed' => true,
        ]);
    }

    #[Test]
    public function it_requires_a_title_when_replacing_with_put(): void
    {
        $task = Task::factory()->create();

        $this->putJson(self::ENDPOINT.'/'.$task->id, ['is_completed' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    #[Test]
    public function it_applies_a_partial_update_with_patch(): void
    {
        $task = Task::factory()->pending()->create([
            'title' => 'Keep this title',
            'description' => 'Keep this description',
        ]);

        $this->patchJson(self::ENDPOINT.'/'.$task->id, ['is_completed' => true])
            ->assertOk()
            ->assertJsonPath('data.title', 'Keep this title')
            ->assertJsonPath('data.description', 'Keep this description')
            ->assertJsonPath('data.is_completed', true);
    }

    #[Test]
    public function it_clears_a_description_when_null_is_sent_explicitly(): void
    {
        $task = Task::factory()->create(['description' => 'Remove me']);

        $this->patchJson(self::ENDPOINT.'/'.$task->id, ['description' => null])
            ->assertOk()
            ->assertJsonPath('data.description', null);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'description' => null]);
    }

    #[Test]
    public function it_returns_not_found_when_updating_a_missing_task(): void
    {
        $this->putJson(self::ENDPOINT.'/999', ['title' => 'Does not matter'])
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------- destroy

    #[Test]
    public function it_deletes_a_task(): void
    {
        $task = Task::factory()->create();

        $this->deleteJson(self::ENDPOINT.'/'.$task->id)
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Task deleted successfully.',
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    #[Test]
    public function it_returns_not_found_when_deleting_a_missing_task(): void
    {
        $this->deleteJson(self::ENDPOINT.'/999')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    // ------------------------------------------------------- routing errors

    #[Test]
    public function it_returns_a_json_error_for_an_unknown_endpoint(): void
    {
        $this->getJson('/api/v1/nope')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The requested endpoint does not exist.');
    }

    #[Test]
    public function it_returns_a_json_error_for_a_disallowed_method(): void
    {
        $task = Task::factory()->create();

        $this->putJson(self::ENDPOINT)
            ->assertStatus(405)
            ->assertJsonPath('success', false);

        $this->postJson(self::ENDPOINT.'/'.$task->id)
            ->assertStatus(405);
    }

    #[Test]
    public function it_ignores_non_numeric_task_identifiers(): void
    {
        $this->getJson(self::ENDPOINT.'/not-an-id')
            ->assertNotFound()
            ->assertJsonPath('message', 'The requested endpoint does not exist.');
    }
}

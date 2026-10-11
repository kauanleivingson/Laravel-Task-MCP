<?php

namespace Tests\Feature;

use App\Mcp\Servers\TaskServer;
use App\Mcp\Tools\CreateTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpTaskToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_tool_schema_can_be_discovered(): void
    {
        TaskServer::tools()->assertRegistered(CreateTask::class);
    }

    public function test_tool_schema_marks_only_title_and_due_date_as_required(): void
    {
        $inputSchema = (new CreateTask)->toArray()['inputSchema'];

        $this->assertSame(['title', 'due_date'], $inputSchema['required']);
    }

    public function test_ai_can_create_a_task_when_due_date_is_provided(): void
    {
        TaskServer::tool(CreateTask::class, [
            'title' => 'Prepare launch checklist',
            'description' => 'Confirm the release steps.',
            'due_date' => '2026-10-12',
        ])
            ->assertOk()
            ->assertStructuredContent([
                'id' => 1,
                'title' => 'Prepare launch checklist',
                'description' => 'Confirm the release steps.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => '2026-10-12',
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Prepare launch checklist',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => '2026-10-12',
        ]);
    }

    public function test_ai_must_collect_due_date_before_creating_a_task(): void
    {
        TaskServer::tool(CreateTask::class, [
            'title' => 'Missing due date',
            'description' => 'This should fail until the user provides a due date.',
        ])->assertHasErrors();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_ai_cannot_create_a_task_with_an_invalid_priority(): void
    {
        TaskServer::tool(CreateTask::class, [
            'title' => 'Invalid task',
            'due_date' => '2026-10-12',
            'priority' => 'urgent',
        ])->assertHasErrors();

        $this->assertDatabaseCount('tasks', 0);
    }
}

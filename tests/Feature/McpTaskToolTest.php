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

    public function test_ai_can_create_a_task_with_default_values(): void
    {
        TaskServer::tool(CreateTask::class, [
            'title' => 'Prepare launch checklist',
            'description' => 'Confirm the release steps.',
        ])
            ->assertOk()
            ->assertStructuredContent([
                'id' => 1,
                'title' => 'Prepare launch checklist',
                'description' => 'Confirm the release steps.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => null,
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Prepare launch checklist',
            'priority' => 'medium',
            'status' => 'pending',
        ]);
    }

    public function test_ai_cannot_create_a_task_with_an_invalid_priority(): void
    {
        TaskServer::tool(CreateTask::class, [
            'title' => 'Invalid task',
            'priority' => 'urgent',
        ])->assertHasErrors();

        $this->assertDatabaseCount('tasks', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Mcp\Servers\TaskServer;
use App\Mcp\Tools\CreateTask;
use App\Mcp\Tools\DeleteTask;
use App\Mcp\Tools\ListTasks;
use App\Mcp\Tools\UpdateTask;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class McpTaskToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_tools_are_registered(): void
    {
        TaskServer::tools()
            ->assertRegistered(CreateTask::class)
            ->assertRegistered(ListTasks::class)
            ->assertRegistered(UpdateTask::class)
            ->assertRegistered(DeleteTask::class);
    }

    public function test_mcp_endpoint_accepts_requests_without_authentication(): void
    {
        $this->postJson('/mcp/tasks', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2026-07-28',
                'capabilities' => [],
                'clientInfo' => [
                    'name' => 'test-client',
                    'version' => '1.0.0',
                ],
                '_meta' => [
                    'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
                    'io.modelcontextprotocol/clientCapabilities' => [],
                ],
            ],
        ], [
            'MCP-Protocol-Version' => '2026-07-28',
            'Mcp-Method' => 'initialize',
        ])->assertOk();

        TaskServer::tool(ListTasks::class)->assertOk();
    }

    public function test_create_schema_marks_only_title_and_due_date_as_required(): void
    {
        $inputSchema = (new CreateTask)->toArray()['inputSchema'];

        $this->assertSame(['title', 'due_date'], $inputSchema['required']);
    }

    public function test_unauthenticated_user_can_create_a_task_when_due_date_is_provided(): void
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
        ]);

        $task = Task::query()->where('title', 'Prepare launch checklist')->firstOrFail();
        $this->assertSame('2026-10-12', $task->due_date->toDateString());
    }

    public function test_admin_must_collect_due_date_before_creating_a_task(): void
    {
        TaskServer::tool(CreateTask::class, [
            'title' => 'Missing due date',
            'description' => 'This should fail until the user provides a due date.',
        ])->assertHasErrors();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_admin_cannot_create_a_task_with_an_invalid_priority(): void
    {
        TaskServer::tool(CreateTask::class, [
            'title' => 'Invalid task',
            'due_date' => '2026-10-12',
            'priority' => 'urgent',
        ])->assertHasErrors();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_unauthenticated_user_can_update_a_task(): void
    {
        $task = Task::create([
            'title' => 'Initial title',
            'due_date' => '2026-10-12',
        ]);

        TaskServer::tool(UpdateTask::class, [
            'id' => $task->id,
            'title' => 'Updated title',
            'status' => 'completed',
        ])
            ->assertOk()
            ->assertStructuredContent([
                'id' => $task->id,
                'title' => 'Updated title',
                'description' => null,
                'priority' => 'medium',
                'status' => 'completed',
                'due_date' => '2026-10-12',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
            'status' => 'completed',
        ]);
    }

    public function test_unauthenticated_user_can_list_tasks(): void
    {
        $task = Task::create([
            'title' => 'Listable task',
            'description' => 'Task details.',
            'due_date' => '2026-10-12',
        ]);

        TaskServer::tool(ListTasks::class)
            ->assertOk()
            ->assertStructuredContent([
                'tasks' => [[
                    'id' => $task->id,
                    'title' => 'Listable task',
                    'description' => 'Task details.',
                    'priority' => 'medium',
                    'status' => 'pending',
                    'due_date' => '2026-10-12',
                ]],
            ]);
    }

    public function test_unauthenticated_user_can_delete_a_task(): void
    {
        $task = Task::create([
            'title' => 'Task to delete',
            'due_date' => '2026-10-12',
        ]);

        TaskServer::tool(DeleteTask::class, ['id' => $task->id])
            ->assertOk()
            ->assertStructuredContent([
                'id' => $task->id,
                'deleted' => true,
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_regular_users_can_use_task_tools(): void
    {
        $this->actingAs(User::factory()->create());

        TaskServer::tool(ListTasks::class)->assertOk();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_database_seeding_creates_configured_admin_user_with_admin_role(): void
    {
        config([
            'app.seed_admin.name' => 'admin',
            'app.seed_admin.email' => 'admin@shopee.com',
            'app.seed_admin.password' => 'test-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin = User::query()->where('email', 'admin@shopee.com')->firstOrFail();

        $this->assertSame('admin', $admin->name);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue(password_verify('test-password', $admin->password));
    }

    public function test_only_admin_users_can_manage_filament_users(): void
    {
        $regularUser = User::factory()->create();
        $admin = $this->actingAsAdmin();

        $this->assertFalse(Gate::forUser($regularUser)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', User::class));
    }

    public function test_users_filament_form_can_be_built_with_role_assignment(): void
    {
        $schema = UserForm::configure(Schema::make());

        $this->assertNotEmpty($schema->getComponents());
    }

    private function actingAsAdmin(): User
    {
        Role::findOrCreate('admin', 'web');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        return $admin;
    }
}

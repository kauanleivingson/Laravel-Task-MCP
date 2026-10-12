<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class ListTasks extends Tool
{
    protected string $name = 'list_tasks';

    protected string $description = 'List all tasks, ordered by most recently created.';

    public function handle(Request $request): ResponseFactory
    {
        $tasks = Task::query()
            ->latest()
            ->get()
            ->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date?->toDateString(),
            ])
            ->all();

        return Response::structured(['tasks' => $tasks]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}

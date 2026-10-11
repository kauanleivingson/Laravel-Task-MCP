<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class CreateTask extends Tool
{
    protected string $name = 'create_task';

    protected string $description = 'Create a task only after collecting its title and due date from the user. If either is missing, ask the user before calling this tool. Never invent or assume required information.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->max(255)->description('A short title for the task.')->required(),
            'description' => $schema->string()->description('Optional additional details about the task.'),
            'priority' => $schema->string()->enum(['low', 'medium', 'high'])->description('Task priority; defaults to medium if omitted.'),
            'status' => $schema->string()->enum(['pending', 'in_progress', 'completed'])->description('Task status; defaults to pending if omitted.'),
            'due_date' => $schema->string()->description('Required due date in YYYY-MM-DD format. Ask the user for this value if it is missing; do not set it to today or now.')->required(),
        ];
    }

    public function handle(Request $request): ResponseFactory
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'required', 'string', 'in:low,medium,high'],
            'status' => ['sometimes', 'required', 'string', 'in:pending,in_progress,completed'],
            'due_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $attributes['priority'] ??= 'medium';
        $attributes['status'] ??= 'pending';

        $task = Task::create($attributes);

        return Response::structured([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_date' => $task->due_date?->toDateString(),
        ]);
    }
}

<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class UpdateTask extends Tool
{
    protected string $name = 'update_task';

    protected string $description = 'Update the provided fields of an existing task. At least one field other than id must be provided.';

    public function handle(Request $request): ResponseFactory
    {
        $attributes = $request->validate([
            'id' => ['required', 'integer', 'exists:tasks,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'required', 'string', 'in:low,medium,high'],
            'status' => ['sometimes', 'required', 'string', 'in:pending,in_progress,completed'],
            'due_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
        ]);

        $providedFields = array_intersect(
            ['title', 'description', 'priority', 'status', 'due_date'],
            array_keys($attributes),
        );

        if ($providedFields === []) {
            throw ValidationException::withMessages([
                'task' => 'At least one task field must be provided.',
            ]);
        }
        $task = Task::findOrFail($attributes['id']);
        $task = Task::findOrFail($attributes['id']);
        unset($attributes['id']);
        $task->update($attributes);

        return Response::structured([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_date' => $task->due_date?->toDateString(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The ID of the task to update.')->required(),
            'title' => $schema->string()->max(255)->description('The task title.'),
            'description' => $schema->string()->description('The task description.'),
            'priority' => $schema->string()->enum(['low', 'medium', 'high'])->description('The task priority.'),
            'status' => $schema->string()->enum(['pending', 'in_progress', 'completed'])->description('The task status.'),
            'due_date' => $schema->string()->description('The due date in YYYY-MM-DD format.'),
        ];
    }
}

<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class DeleteTask extends Tool
{
    protected string $name = 'delete_task';

    protected string $description = 'Permanently delete a task by its ID.';

    public function handle(Request $request): ResponseFactory
    {
        $attributes = $request->validate([
            'id' => ['required', 'integer', 'exists:tasks,id'],
        ]);
        $task = Task::findOrFail($attributes['id']);
        $task->delete();

        return Response::structured([
            'id' => $task->id,
            'deleted' => true,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The ID of the task to delete.')->required(),
        ];
    }
}

<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateTask;
use App\Mcp\Tools\DeleteTask;
use App\Mcp\Tools\ListTasks;
use App\Mcp\Tools\UpdateTask;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Task Server')]
#[Version('0.0.1')]
#[Instructions('Use create_task, list_tasks, update_task, and delete_task for task management. For create_task, collect the required title and due date from the user; ask for either if missing and never invent required information.')]
class TaskServer extends Server
{
    protected array $tools = [
        CreateTask::class,
        ListTasks::class,
        UpdateTask::class,
        DeleteTask::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}

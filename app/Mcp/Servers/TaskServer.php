<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateTask;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Task Server')]
#[Version('0.0.1')]
#[Instructions('Create and manage tasks in the application. Use create_task to save a task with a title and optional details.')]
class TaskServer extends Server
{
    protected array $tools = [
        CreateTask::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}

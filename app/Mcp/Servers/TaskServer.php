<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateTask;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Task Server')]
#[Version('0.0.1')]
#[Instructions('Create tasks with create_task only after collecting the required title and due date from the user. If either is missing, ask the user before calling the tool. Never invent or assume required information. The description, priority, and status are optional; use the tool defaults when they are not provided.')]
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

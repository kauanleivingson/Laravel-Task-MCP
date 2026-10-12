<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}

<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\TaskRepository;

class PdoTaskRepository implements TaskRepository
{
    public function allTasks(): array
    {
        return array();
    }
}

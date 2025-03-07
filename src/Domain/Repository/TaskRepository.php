<?php

namespace SoftplanTasksApi\Domain\Repository;

interface TaskRepository
{
    public function allTasks(): array;
}

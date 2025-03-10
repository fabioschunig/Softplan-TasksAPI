<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\TaskRepository;

class PdoTaskRepository implements TaskRepository
{
    public function allTasks(): array
    {
        return array();
    }

    public function searchTasks(string|null $searchText, \DateTime|null $startDate, \DateTime|null $endDate): array
    {
        return array();
    }
}

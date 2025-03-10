<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\TaskRepository;
use PDO;

class PdoTaskRepository implements TaskRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function allTasks(): array
    {
        return array();
    }

    public function searchTasks(string|null $searchText, \DateTime|null $startDate, \DateTime|null $endDate): array
    {
        return array();
    }
}

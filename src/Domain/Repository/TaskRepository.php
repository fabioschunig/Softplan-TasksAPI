<?php

namespace SoftplanTasksApi\Domain\Repository;

interface TaskRepository
{
    public function allTasks(): array;
    public function searchTasks(
        string|null $searchText,
        \DateTime|null $startDate,
        \DateTime|null $endDate,
        string|null $statusFilter = null
    ): array;

    public function findById(int $id): ?\SoftplanTasksApi\Domain\Model\Task;

    public function create(\SoftplanTasksApi\Domain\Model\Task $task): bool;

    public function update(\SoftplanTasksApi\Domain\Model\Task $task): bool;

    public function delete(int $id): bool;
}

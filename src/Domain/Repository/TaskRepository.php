<?php

namespace SoftplanTasksApi\Domain\Repository;

interface TaskRepository
{
    public function allTasks(): array;
    public function searchTasks(
        string|null $searchText,
        \DateTime|null $startDate,
        \DateTime|null $endDate
    ): array;
}

<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\ProjectRepository;

class PdoProjectRepository implements ProjectRepository
{

    public function allProjects(): array
    {
        // Implement logic to retrieve all projects
        return [];
    }

    public function searchProjects(
        string|null $searchText,
        \DateTime|null $startDate,
        \DateTime|null $endDate
    ): array {
        // Implement logic to retrieve all projects
        return [];
    }
}

<?php

namespace SoftplanTasksApi\Domain\Repository;

interface ProjectRepository
{
    public function allProjects(): array;
    public function searchProjects(
        string|null $searchText,
        \DateTime|null $startDate,
        \DateTime|null $endDate
    ): array;
}

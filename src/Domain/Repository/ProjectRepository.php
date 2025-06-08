<?php

namespace SoftplanTasksApi\Domain\Repository;

use SoftplanTasksApi\Domain\Model\Project;

interface ProjectRepository
{
    public function findAll(): array;
    public function findById(int $id): ?Project;
    public function create(string $description): Project;
    public function update(int $id, string $description): ?Project;
    public function delete(int $id): bool;
    public function searchProjects(
        string|null $searchText,
        \DateTime|null $startDate,
        \DateTime|null $endDate
    ): array;
}

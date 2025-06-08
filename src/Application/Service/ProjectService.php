<?php

namespace SoftplanTasksApi\Application\Service;

use SoftplanTasksApi\Domain\Repository\ProjectRepository;
use SoftplanTasksApi\Domain\Model\Project;

class ProjectService
{
    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function getAllProjects(): array
    {
        $projects = $this->projectRepository->findAll();
        
        return array_map(function (Project $project) {
            return $this->projectToArray($project);
        }, $projects);
    }

    public function getProjectById(int $id): array|null
    {
        $project = $this->projectRepository->findById($id);
        
        if (!$project) {
            return null;
        }
        
        return $this->projectToArray($project);
    }

    public function createProject(string $description): array|null
    {
        // Validate description
        if (empty(trim($description))) {
            return null;
        }

        if (strlen($description) > 255) {
            return null;
        }

        $project = $this->projectRepository->create(trim($description));
        
        return $this->projectToArray($project);
    }

    public function updateProject(int $id, string $description): array|null
    {
        // Validate description
        if (empty(trim($description))) {
            return null;
        }

        if (strlen($description) > 255) {
            return null;
        }

        $project = $this->projectRepository->update($id, trim($description));
        
        if (!$project) {
            return null;
        }
        
        return $this->projectToArray($project);
    }

    public function deleteProject(int $id): bool
    {
        return $this->projectRepository->delete($id);
    }

    public function searchProjects(
        string|null $searchText = null,
        \DateTime|null $startDate = null,
        \DateTime|null $endDate = null
    ): array {
        $projects = $this->projectRepository->searchProjects($searchText, $startDate, $endDate);
        
        return array_map(function (Project $project) {
            return $this->projectToArray($project);
        }, $projects);
    }

    private function projectToArray(Project $project): array
    {
        return [
            'id' => $project->id,
            'description' => $project->description,
            'created' => $project->created?->format('Y-m-d H:i:s'),
            'updated' => $project->updated?->format('Y-m-d H:i:s')
        ];
    }
}

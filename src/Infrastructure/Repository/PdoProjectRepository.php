<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\ProjectRepository;
use SoftplanTasksApi\Domain\Model\Project;
use PDO;
use DateTime;

class PdoProjectRepository implements ProjectRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM project ORDER BY created DESC");
        $stmt->execute();
        
        $projects = [];
        while ($projectData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $projects[] = $this->mapToProject($projectData);
        }
        
        return $projects;
    }

    public function findById(int $id): ?Project
    {
        $stmt = $this->connection->prepare("SELECT * FROM project WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$projectData) {
            return null;
        }
        
        return $this->mapToProject($projectData);
    }

    public function create(string $description): Project
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO project (description, created) VALUES (:description, NOW())"
        );
        
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        
        $projectId = $this->connection->lastInsertId();
        
        return $this->findById((int)$projectId);
    }

    public function update(int $id, string $description): ?Project
    {
        $stmt = $this->connection->prepare(
            "UPDATE project SET description = :description, updated = NOW() WHERE id = :id"
        );
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':description', $description);
        
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return $this->findById($id);
        }
        
        return null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM project WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function searchProjects(
        string|null $searchText,
        \DateTime|null $startDate,
        \DateTime|null $endDate
    ): array {
        $sql = "SELECT * FROM project WHERE 1=1";
        $params = [];
        
        if ($searchText) {
            $sql .= " AND description LIKE :searchText";
            $params[':searchText'] = '%' . $searchText . '%';
        }
        
        if ($startDate) {
            $sql .= " AND created >= :startDate";
            $params[':startDate'] = $startDate->format('Y-m-d H:i:s');
        }
        
        if ($endDate) {
            $sql .= " AND created <= :endDate";
            $params[':endDate'] = $endDate->format('Y-m-d H:i:s');
        }
        
        $sql .= " ORDER BY created DESC";
        
        $stmt = $this->connection->prepare($sql);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        
        $projects = [];
        while ($projectData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $projects[] = $this->mapToProject($projectData);
        }
        
        return $projects;
    }

    private function mapToProject(array $projectData): Project
    {
        return new Project(
            $projectData['id'],
            $projectData['description'],
            new DateTime($projectData['created']),
            $projectData['updated'] ? new DateTime($projectData['updated']) : null
        );
    }
}

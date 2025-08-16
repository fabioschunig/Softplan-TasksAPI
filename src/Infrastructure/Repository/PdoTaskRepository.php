<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Model\Task;
use SoftplanTasksApi\Domain\Repository\TaskRepository;
use PDO;
use DateTime;

class PdoTaskRepository implements TaskRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function allTasks(): array
    {
        return $this->searchTasks(null, null, null);
    }

    public function searchTasks(
        string|null $searchText,
        DateTime|null $startDate,
        DateTime|null $endDate
    ): array {
        $sqlQuery = "SELECT t.* FROM task t LEFT JOIN project p ON t.project_id = p.id WHERE 1=1";

        if ($searchText) {
            $sqlQuery .= " AND (t.description LIKE :searchText OR t.tags LIKE :searchText OR p.description LIKE :searchText)";
        }

        if ($startDate && $endDate) {
            $sqlQuery .= " AND t.reference_date BETWEEN :startDate AND :endDate";
        } elseif ($startDate) {
            $sqlQuery .= " AND t.reference_date >= :startDate";
        } elseif ($endDate) {
            $sqlQuery .= " AND t.reference_date <= :endDate";
        }

        // Add default ordering by finished date descending (NULL values last)
        $sqlQuery .= " ORDER BY t.finished DESC, t.id DESC";

        $stmt = $this->connection->prepare($sqlQuery);

        if ($searchText) {
            $textLike = ('%' . $searchText . '%');
            $stmt->bindParam('searchText', $textLike);
        }

        if ($startDate) {
            $startDateFormat = $startDate->format('Y-m-d');
            $stmt->bindParam('startDate', $startDateFormat);
        }

        if ($endDate) {
            $endDateFormat = $endDate->format('Y-m-d');
            $stmt->bindParam('endDate', $endDateFormat);
        }

        $stmt->execute();
        $taskDataList = $stmt->fetchAll();

        $taskList = [];
        foreach ($taskDataList as $taskData) {
            $task = new Task(
                $taskData['id'],
                $taskData['description'],
                $taskData['tags'],
                $taskData['project_id'],
                $taskData['reference_date'] == null ? null : new DateTime($taskData['reference_date']),
                $taskData['finished']  == null ? null : new DateTime($taskData['finished']),
                $taskData['created'] == null ? null : new DateTime($taskData['created']),
                $taskData['updated']  == null ? null : new DateTime($taskData['updated']),
            );

            $taskList[] = $task;
        }

        return $taskList;
    }

    public function findById(int $id): ?Task
    {
        $stmt = $this->connection->prepare('SELECT * FROM task WHERE id = ?');
        $stmt->execute([$id]);
        $taskData = $stmt->fetch();

        if (!$taskData) {
            return null;
        }

        return $this->hydrateTask($taskData);
    }

    public function create(Task $task): bool
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO task (description, tags, project_id, reference_date, finished) VALUES (?, ?, ?, ?, ?)'
        );

        return $stmt->execute([
            $task->description,
            $task->tags,
            $task->projectId,
            $task->reference_date ? $task->reference_date->format('Y-m-d H:i:s') : null,
            $task->finished ? $task->finished->format('Y-m-d H:i:s') : null
        ]);
    }

    public function update(Task $task): bool
    {
        $stmt = $this->connection->prepare(
            'UPDATE task SET description = ?, tags = ?, project_id = ?, reference_date = ?, finished = ? WHERE id = ?'
        );

        return $stmt->execute([
            $task->description,
            $task->tags,
            $task->projectId,
            $task->reference_date ? $task->reference_date->format('Y-m-d H:i:s') : null,
            $task->finished ? $task->finished->format('Y-m-d H:i:s') : null,
            $task->id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM task WHERE id = ?');
        return $stmt->execute([$id]);
    }

    private function hydrateTask(array $taskData): Task
    {
        return new Task(
            $taskData['id'],
            $taskData['description'],
            $taskData['tags'],
            $taskData['project_id'],
            $taskData['reference_date'] == null ? null : new DateTime($taskData['reference_date']),
            $taskData['finished']  == null ? null : new DateTime($taskData['finished']),
            $taskData['created'] == null ? null : new DateTime($taskData['created']),
            $taskData['updated']  == null ? null : new DateTime($taskData['updated'])
        );
    }
}

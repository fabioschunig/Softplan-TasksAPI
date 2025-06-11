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
        $sqlQuery = "SELECT * FROM task WHERE 1=1";

        if ($searchText) {
            $sqlQuery .= " AND description like :searchText";
        }

        if ($startDate) {
            $sqlQuery .= " AND started >= :startDate";
        }

        if ($endDate) {
            $sqlQuery .= " AND finished <= :endDate";
        }

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
                $taskData['started'] == null ? null : new DateTime($taskData['started']),
                $taskData['finished']  == null ? null : new DateTime($taskData['finished']),
                $taskData['status'],
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
            'INSERT INTO task (description, tags, project_id, status) VALUES (?, ?, ?, ?)'
        );

        return $stmt->execute([
            $task->description,
            $task->tags,
            $task->projectId,
            $task->status
        ]);
    }

    public function update(Task $task): bool
    {
        $stmt = $this->connection->prepare(
            'UPDATE task SET description = ?, tags = ?, project_id = ?, started = ?, finished = ?, status = ? WHERE id = ?'
        );

        return $stmt->execute([
            $task->description,
            $task->tags,
            $task->projectId,
            $task->started ? $task->started->format('Y-m-d H:i:s') : null,
            $task->finished ? $task->finished->format('Y-m-d H:i:s') : null,
            $task->status,
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
            $taskData['started'] == null ? null : new DateTime($taskData['started']),
            $taskData['finished']  == null ? null : new DateTime($taskData['finished']),
            $taskData['status'],
            $taskData['created'] == null ? null : new DateTime($taskData['created']),
            $taskData['updated']  == null ? null : new DateTime($taskData['updated'])
        );
    }
}

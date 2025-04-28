<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\TaskRepository;
use SoftplanTasksApi\Domain\Model\Task;
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
        return $this->searchTasks(null, null, null);
    }

    public function searchTasks(string|null $searchText, \DateTime|null $startDate, \DateTime|null $endDate): array
    {
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
                $taskData['started'] == null ? null : new \DateTime($taskData['started']),
                $taskData['finished']  == null ? null : new \DateTime($taskData['finished']),
                $taskData['status'],
                $taskData['created'] == null ? null : new \DateTime($taskData['created']),
                $taskData['updated']  == null ? null : new \DateTime($taskData['updated']),
            );

            $taskList[] = $task;
        }

        return $taskList;
    }
}

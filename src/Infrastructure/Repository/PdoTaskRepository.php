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
        $sqlQuery = 'SELECT * FROM tarefa;';
        $stmt = $this->connection->query($sqlQuery);

        $taskDataList = $stmt->fetchAll();
        $taskList = [];

        foreach ($taskDataList as $taskData) {
            $task = new Task(
                $taskData['id'],
                $taskData['descricao'],
                $taskData['referencia'] ? null : new \DateTime($taskData['referencia']),
                $taskData['inicio'] ? null : new \DateTime($taskData['inicio']),
                $taskData['fim'] ? null : new \DateTime($taskData['fim']),
                $taskData['observacao'],
                $taskData['origem'],
            );

            $taskList[] = $task;
        }

        return $taskList;
    }

    public function searchTasks(string|null $searchText, \DateTime|null $startDate, \DateTime|null $endDate): array
    {
        return array();
    }
}

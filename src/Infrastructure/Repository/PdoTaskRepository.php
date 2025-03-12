<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\TaskRepository;
use SoftplanTasksApi\Domain\Model\Task;
use PDO;

class PdoTaskRepository implements TaskRepository
{
    private PDO $connection;
    private int $responsavel = 0;
    private int $projeto = 0;

    public function __construct(PDO $connection, int $responsavel, int $projeto)
    {
        $this->connection = $connection;

        $this->responsavel = $responsavel;
        if ($this->responsavel <= 0) {
            throw new \Exception("Obrigatório informar um responsável pelas tarefas");
        }

        $this->projeto = $projeto;
        if ($this->projeto <= 0) {
            throw new \Exception("Obrigatório informar um projeto para selecionar tarefas");
        }
    }

    public function allTasks(): array
    {
        return $this->searchTasks(null, null, null);
    }

    public function searchTasks(string|null $searchText, \DateTime|null $startDate, \DateTime|null $endDate): array
    {
        $sqlQuery = "SELECT * FROM tarefa WHERE responsavel = :responsavel AND projeto = :projeto";

        if ($searchText) {
            $sqlQuery .= " AND descricao like :searchText";
        }

        if ($startDate) {
            $sqlQuery .= " AND inicio >= :startDate";
        }

        if ($endDate) {
            $sqlQuery .= " AND fim <= :endDate";
        }

        $stmt = $this->connection->prepare($sqlQuery);
        $stmt->bindParam('responsavel', $this->responsavel);
        $stmt->bindParam('projeto', $this->projeto);

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
                $taskData['descricao'],
                $taskData['referencia'] == null ? null : new \DateTime($taskData['referencia']),
                $taskData['inicio'] == null ? null : new \DateTime($taskData['inicio']),
                $taskData['fim']  == null ? null : new \DateTime($taskData['fim']),
                $taskData['observacao'],
                $taskData['origem'],
            );

            $taskList[] = $task;
        }

        return $taskList;
    }
}

<?php

namespace SoftplanTasksApi\Application\Service;

use SoftplanTasksApi\Domain\Repository\TaskRepository;
use SoftplanTasksApi\Domain\Model\Task;
use InvalidArgumentException;

class TaskService
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getAllTasks(): array
    {
        return $this->taskRepository->allTasks();
    }

    public function getTaskById(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    public function createTask(array $data): bool
    {
        if (empty($data['description']) || strlen($data['description']) > 255) {
            throw new InvalidArgumentException('A descrição da tarefa é obrigatória e deve ter no máximo 255 caracteres.');
        }

        $reference_date = isset($data['reference_date']) && $data['reference_date'] ? new \DateTime($data['reference_date']) : null;
        $finished = isset($data['finished']) && $data['finished'] ? new \DateTime($data['finished']) : null;

        if ($reference_date && $finished && $finished < $reference_date) {
            throw new InvalidArgumentException('A data de término não pode ser anterior à data de início.');
        }

        $task = new Task(
            0, // ID será gerado pelo banco
            $data['description'],
            $data['tags'] ?? null,
            $data['project_id'] ?? null,
            $reference_date,
            $finished,
            null, // created
            null // updated
        );

        return $this->taskRepository->create($task);
    }

    public function updateTask(int $id, array $data): bool
    {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            return false; // Ou lançar uma exceção
        }

        // Atualiza os campos do objeto task com os novos dados
        $updatedTask = new Task(
            $id,
            $data['description'] ?? $task->description,
            $data['tags'] ?? $task->tags,
            $data['project_id'] ?? $task->projectId,
            isset($data['reference_date']) ? new \DateTime($data['reference_date']) : $task->reference_date,
            isset($data['finished']) ? new \DateTime($data['finished']) : $task->finished,
            $task->created,
            new \DateTime() // updated
        );

        if (strlen($updatedTask->description) > 255) {
            throw new InvalidArgumentException('A descrição da tarefa deve ter no máximo 255 caracteres.');
        }

        if ($updatedTask->reference_date && $updatedTask->finished && $updatedTask->finished < $updatedTask->reference_date) {
            throw new InvalidArgumentException('A data de término não pode ser anterior à data de início.');
        }

        return $this->taskRepository->update($updatedTask);
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskRepository->delete($id);
    }

    public function searchTasks(string $searchText = null, string $startDate = null, string $endDate = null, string $statusFilter = null): array
    {
        $startDateTime = $startDate ? new \DateTime($startDate) : null;
        $endDateTime = $endDate ? new \DateTime($endDate) : null;
        return $this->taskRepository->searchTasks($searchText, $startDateTime, $endDateTime, $statusFilter);
    }
}

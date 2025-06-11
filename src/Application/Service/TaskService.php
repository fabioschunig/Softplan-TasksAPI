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

        // O modelo Task espera todos os campos no construtor, vamos preencher com valores padrão
        $task = new Task(
            0, // ID será gerado pelo banco
            $data['description'],
            $data['tags'] ?? null,
            $data['project_id'] ?? null,
            null, // started
            null, // finished
            $data['status'] ?? 0,
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
            isset($data['started']) ? new \DateTime($data['started']) : $task->started,
            isset($data['finished']) ? new \DateTime($data['finished']) : $task->finished,
            $data['status'] ?? $task->status,
            $task->created,
            new \DateTime() // updated
        );

        if (strlen($updatedTask->description) > 255) {
            throw new InvalidArgumentException('A descrição da tarefa deve ter no máximo 255 caracteres.');
        }

        return $this->taskRepository->update($updatedTask);
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskRepository->delete($id);
    }

    public function searchTasks(string $searchText = null, string $startDate = null, string $endDate = null): array
    {
        $startDateTime = $startDate ? new \DateTime($startDate) : null;
        $endDateTime = $endDate ? new \DateTime($endDate) : null;
        return $this->taskRepository->searchTasks($searchText, $startDateTime, $endDateTime);
    }
}

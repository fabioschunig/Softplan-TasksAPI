<?php

use SoftplanTasksApi\Infrastructure\Repository\PdoTaskRepository;

require_once 'vendor/autoload.php';

include_once 'pdo-connection.php';

echo "Connection: \n";
var_dump($pdoConnection);

$taskRepository = new PdoTaskRepository(
    $pdoConnection,
    $config->getResponsavel(),
    $config->getProjeto(),
);

$tasks = $taskRepository->allTasks();
echo "All Tasks: \n";
var_dump($tasks);

$searchTasks = $taskRepository->searchTasks(null, null, null);
echo "Search Tasks: \n";
var_dump($searchTasks);

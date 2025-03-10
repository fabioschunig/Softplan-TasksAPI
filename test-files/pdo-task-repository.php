<?php

use SoftplanTasksApi\Infrastructure\Repository\PdoTaskRepository;

require_once 'vendor/autoload.php';

include_once 'pdo-connection.php';

echo "Connection: \n";
var_dump($pdoConnection);

$taskRepository = new PdoTaskRepository();

$tasks = $taskRepository->allTasks();
echo "All Tasks: \n";
var_dump($tasks);

<?php

use SoftplanTasksApi\Infrastructure\Repository\PdoTaskRepository;

require_once 'vendor/autoload.php';

include_once 'pdo-connection.php';

echo "Connection: \n";
var_dump($pdoConnection);

$taskRepository = new PdoTaskRepository(
    $pdoConnection,
);

$tasks = $taskRepository->allTasks();
echo "All Tasks: \n";
var_dump($tasks);
echo "End - All Tasks \n";

$searchTasks = $taskRepository->searchTasks(
    'cliente',
    new \DateTime('2021-08-25'),
    new \DateTime('2021-08-28'),
);
echo "Search Tasks: \n";
var_dump($searchTasks);
echo "End - Search Tasks \n";

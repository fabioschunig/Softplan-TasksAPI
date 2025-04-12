<?php

use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;
use SoftplanTasksApi\Infrastructure\Repository\PdoTaskRepository;

require_once '../vendor/autoload.php';

$config = new ConfigAppEnvFile;
$config->loadEnv();

$pdoConnection = \SoftplanTasksApi\Infrastructure\Persistence\PdoConnectionCreator::createConnection(
    $config->getHost(),
    $config->getDBName(),
    $config->getUsername(),
    $config->getPassword(),
);

$taskRepository = new PdoTaskRepository(
    $pdoConnection,
);

$tasks = $taskRepository->allTasks();

// API response in JSON
header('Content-Type: application/json');
echo json_encode([
    'tasks' => $tasks,
]);

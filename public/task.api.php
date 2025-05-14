<?php

use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;
use SoftplanTasksApi\Infrastructure\Repository\PdoTaskRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoUserRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoSessionRepository;
use SoftplanTasksApi\Application\Service\AuthService;
use SoftplanTasksApi\Infrastructure\Middleware\AuthMiddleware;

require_once '../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$config = new ConfigAppEnvFile;
$config->loadEnv();

$pdoConnection = \SoftplanTasksApi\Infrastructure\Persistence\PdoConnectionCreator::createConnection(
    $config->getHost(),
    $config->getDBName(),
    $config->getUsername(),
    $config->getPassword(),
);

// Setup authentication
$userRepository = new PdoUserRepository($pdoConnection);
$sessionRepository = new PdoSessionRepository($pdoConnection);
$authService = new AuthService($userRepository, $sessionRepository);
$authMiddleware = new AuthMiddleware($authService);

// Require authentication
$user = $authMiddleware->requireAuth();

$taskRepository = new PdoTaskRepository($pdoConnection);
$tasks = $taskRepository->allTasks();

// API response in JSON
echo json_encode([
    'tasks' => $tasks,
    'user' => [
        'id' => $user->id,
        'username' => $user->username
    ]
]);

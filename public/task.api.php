<?php

use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;
use SoftplanTasksApi\Infrastructure\Repository\PdoTaskRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoUserRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoSessionRepository;
use SoftplanTasksApi\Application\Service\TaskService;
use SoftplanTasksApi\Application\Service\AuthService;
use SoftplanTasksApi\Infrastructure\Middleware\AuthMiddleware;
use SoftplanTasksApi\Infrastructure\Middleware\AuthorizationMiddleware;

require_once '../vendor/autoload.php';

// Disable HTML error output to prevent JSON corruption
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Global error handler for non-fatal errors
set_error_handler(function($severity, $message, $file, $line) {
    $errorMessage = "PHP Error: $message in $file on line $line";
    error_log("[ERROR] $errorMessage");
    
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Ocorreu um erro interno no servidor',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    exit();
});

// Global exception handler
set_exception_handler(function($exception) {
    $errorMessage = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log("[EXCEPTION] $errorMessage");
    
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Ocorreu um erro interno no servidor',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    exit();
});

// Fatal error handler using output buffering
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $errorMessage = "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
        error_log("[FATAL] $errorMessage");
        
        // Clear any output buffer to prevent HTML error display
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Ocorreu um erro fatal no servidor',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        exit();
    }
});

// Start output buffering to capture any unexpected output
ob_start();

// Function to log errors and send standardized response
function handleError($message, $statusCode = 500, $logLevel = 'ERROR') {
    error_log("[$logLevel] Tasks API Error: $message");
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Function to send success response
function sendSuccess($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $config = new ConfigAppEnvFile;
    $config->loadEnv();
} catch (Exception $e) {
    handleError('Erro de configuração: ' . $e->getMessage(), 500);
}

try {
    $pdoConnection = \SoftplanTasksApi\Infrastructure\Persistence\PdoConnectionCreator::createConnection(
        $config->getHost(),
        $config->getDBName(),
        $config->getUsername(),
        $config->getPassword(),
    );
    
    // Validate database connection
    if (!$pdoConnection) {
        handleError('Falha na conexão com o banco de dados. Por favor, verifique sua configuração.', 500);
    }
    
    // Test the connection with a simple query
    $pdoConnection->query('SELECT 1');
    
} catch (PDOException $e) {
    handleError('Falha na conexão com o banco de dados: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    handleError('Erro na configuração do banco de dados: ' . $e->getMessage(), 500);
}

try {
    $taskRepository = new PdoTaskRepository($pdoConnection);
    $taskService = new TaskService($taskRepository);

    // Auth services for middleware
    $userRepository = new PdoUserRepository($pdoConnection);
    $sessionRepository = new PdoSessionRepository($pdoConnection);
    $authService = new AuthService($userRepository, $sessionRepository);
    $authMiddleware = new AuthMiddleware($authService);
} catch (Exception $e) {
    handleError('Erro na inicialização do serviço: ' . $e->getMessage(), 500);
}

// Authenticate user for all requests
try {
    $user = $authMiddleware->authenticate();
    if (!$user) {
        handleError('Autenticação necessária', 401);
    }
} catch (Exception $e) {
    handleError('Erro de autenticação: ' . $e->getMessage(), 401);
}

$method = $_SERVER['REQUEST_METHOD'];

$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

$pathInfo = '';
if (strpos($requestUri, $scriptName) === 0) {
    $pathInfo = substr($requestUri, strlen($scriptName));
} else {
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
}

if (($pos = strpos($pathInfo, '?')) !== false) {
    $pathInfo = substr($pathInfo, 0, $pos);
}

$segments = array_filter(explode('/', trim($pathInfo, '/')));
$taskId = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;

try {
    switch ($method) {
        case 'GET':
            if ($taskId) {
                $task = $taskService->getTaskById($taskId);
                if ($task) {
                    sendSuccess($task);
                } else {
                    handleError('Tarefa não encontrada', 404);
                }
            } else {
                $searchText = $_GET['search'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $tasks = $taskService->searchTasks($searchText, $startDate, $endDate);
                sendSuccess($tasks);
            }
            break;

        case 'POST':
            // Create task (Admin only)
            if (!AuthorizationMiddleware::canCreateTask($user)) {
                handleError('Acesso negado. Privilégios de administrador são necessários para criar tarefas.', 403);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                handleError('Entrada JSON inválida', 400);
            }
            $result = $taskService->createTask($input);
            if ($result) {
                sendSuccess(['message' => 'Tarefa criada com sucesso'], 201);
            } else {
                handleError('Falha ao criar tarefa', 400);
            }
            break;

        case 'PUT':
            // Update task (Admin only)
            if (!AuthorizationMiddleware::canEditTask($user)) {
                handleError('Acesso negado. Privilégios de administrador são necessários para editar tarefas.', 403);
            }
            
            if (!$taskId) {
                handleError('O ID da tarefa é obrigatório para atualização', 400);
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                handleError('Entrada JSON inválida', 400);
            }
            $result = $taskService->updateTask($taskId, $input);
            if ($result) {
                sendSuccess(['message' => 'Tarefa atualizada com sucesso']);
            } else {
                handleError('Tarefa não encontrada ou falha na atualização', 404);
            }
            break;

        case 'DELETE':
            // Delete task (Admin only)
            if (!AuthorizationMiddleware::canDeleteTask($user)) {
                handleError('Acesso negado. Privilégios de administrador são necessários para excluir tarefas.', 403);
            }
            
            if (!$taskId) {
                handleError('O ID da tarefa é obrigatório para exclusão', 400);
            }
            $result = $taskService->deleteTask($taskId);
            if ($result) {
                sendSuccess(['message' => 'Tarefa excluída com sucesso']);
            } else {
                handleError('Tarefa não encontrada', 404);
            }
            break;

        default:
            handleError('Método não permitido', 405);
            break;
    }
} catch (InvalidArgumentException $e) {
    handleError($e->getMessage(), 400);
} catch (Exception $e) {
    handleError('Ocorreu um erro inesperado: ' . $e->getMessage(), 500);
}

<?php

use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;
use SoftplanTasksApi\Infrastructure\Repository\PdoProjectRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoUserRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoSessionRepository;
use SoftplanTasksApi\Application\Service\ProjectService;
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
    error_log("[$logLevel] API Error: $message");
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
    $projectRepository = new PdoProjectRepository($pdoConnection);
    $projectService = new ProjectService($projectRepository);
    
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

// Handle URL parsing for both Apache and PHP built-in server
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Remove script name from request URI to get the path info
$pathInfo = '';
if (strpos($requestUri, $scriptName) === 0) {
    $pathInfo = substr($requestUri, strlen($scriptName));
} else {
    // For cases where the script name is not in the URI (like with .htaccess rewrite)
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
}

// Remove query string if present
if (($pos = strpos($pathInfo, '?')) !== false) {
    $pathInfo = substr($pathInfo, 0, $pos);
}

$segments = array_filter(explode('/', trim($pathInfo, '/')));
$projectId = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;

try {
    switch ($method) {
        case 'GET':
            if ($projectId) {
                // GET /project.api.php/123 - Get specific project
                try {
                    $project = $projectService->getProjectById($projectId);
                    
                    if ($project) {
                        sendSuccess($project);
                    } else {
                        handleError('Projeto não encontrado', 404);
                    }
                } catch (Exception $e) {
                    handleError('Erro ao buscar projeto: ' . $e->getMessage(), 500);
                }
            } else {
                // GET /project.api.php - Get all projects or search
                try {
                    $searchText = $_GET['search'] ?? null;
                    $startDate = isset($_GET['start_date']) ? new DateTime($_GET['start_date']) : null;
                    $endDate = isset($_GET['end_date']) ? new DateTime($_GET['end_date']) : null;
                    
                    if ($searchText || $startDate || $endDate) {
                        $projects = $projectService->searchProjects($searchText, $startDate, $endDate);
                    } else {
                        $projects = $projectService->getAllProjects();
                    }
                    
                    sendSuccess($projects);
                } catch (Exception $e) {
                    handleError('Erro ao buscar projetos: ' . $e->getMessage(), 500);
                }
            }
            break;
            
        case 'POST':
            // POST /project.api.php - Create new project (Admin only)
            if (!AuthorizationMiddleware::canCreateProject($user)) {
                handleError('Acesso negado. Privilégios de administrador são necessários para criar projetos.', 403);
            }
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['description'])) {
                    handleError('A descrição é obrigatória', 400);
                }
                
                $project = $projectService->createProject($input['description']);
                
                if ($project) {
                    sendSuccess($project, 201);
                } else {
                    handleError('Falha ao criar projeto. A descrição não pode ser vazia ou exceder 255 caracteres.', 400);
                }
            } catch (Exception $e) {
                handleError('Erro ao criar projeto: ' . $e->getMessage(), 500);
            }
            break;
            
        case 'PUT':
            // PUT /project.api.php/123 - Update specific project (Admin only)
            if (!AuthorizationMiddleware::canEditProject($user)) {
                handleError('Acesso negado. Privilégios de administrador são necessários para editar projetos.', 403);
            }
            
            if (!$projectId) {
                handleError('O ID do projeto é obrigatório para atualização', 400);
            }
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['description'])) {
                    handleError('A descrição é obrigatória', 400);
                }
                
                $project = $projectService->updateProject($projectId, $input['description']);
                
                if ($project) {
                    sendSuccess($project);
                } else {
                    handleError('Projeto não encontrado ou descrição inválida', 404);
                }
            } catch (Exception $e) {
                handleError('Erro ao atualizar projeto: ' . $e->getMessage(), 500);
            }
            break;
            
        case 'DELETE':
            // DELETE /project.api.php/123 - Delete specific project (Admin only)
            if (!AuthorizationMiddleware::canDeleteProject($user)) {
                handleError('Acesso negado. Privilégios de administrador são necessários para excluir projetos.', 403);
            }
            
            if (!$projectId) {
                handleError('O ID do projeto é obrigatório para exclusão', 400);
            }
            
            try {
                $result = $projectService->deleteProject($projectId);
                
                if ($result) {
                    sendSuccess(['message' => 'Projeto excluído com sucesso']);
                } else {
                    handleError('Projeto não encontrado', 404);
                }
            } catch (Exception $e) {
                handleError('Erro ao excluir projeto: ' . $e->getMessage(), 500);
            }
            break;
            
        default:
            handleError('Método não permitido', 405);
            break;
    }
} catch (Exception $e) {
    handleError('Erro inesperado: ' . $e->getMessage(), 500);
}

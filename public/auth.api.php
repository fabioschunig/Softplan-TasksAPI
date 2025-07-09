<?php

use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;
use SoftplanTasksApi\Infrastructure\Repository\PdoUserRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoSessionRepository;
use SoftplanTasksApi\Application\Service\AuthService;

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
            'error' => 'Internal server error occurred',
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
            'error' => 'Internal server error occurred',
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
                'error' => 'Fatal server error occurred',
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
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $config = new ConfigAppEnvFile;
    $config->loadEnv();
} catch (Exception $e) {
    handleError('Configuration error: ' . $e->getMessage(), 500);
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
        handleError('Database connection failed. Please check your database configuration.', 500);
    }
    
    // Test the connection with a simple query
    $pdoConnection->query('SELECT 1');
    
} catch (PDOException $e) {
    handleError('Database connection failed: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    handleError('Database setup error: ' . $e->getMessage(), 500);
}


try {
    $userRepository = new PdoUserRepository($pdoConnection);
    $sessionRepository = new PdoSessionRepository($pdoConnection);
    $authService = new AuthService($userRepository, $sessionRepository);
} catch (Exception $e) {
    handleError('Service initialization error: ' . $e->getMessage(), 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($path) {
                case 'login':
                    try {
                        if (!isset($input['username']) || !isset($input['password'])) {
                            handleError('Username and password are required', 400);
                        }
                        
                        $result = $authService->login($input['username'], $input['password']);
                        
                        if ($result) {
                            sendSuccess($result);
                        } else {
                            handleError('Invalid credentials', 401);
                        }
                    } catch (Exception $e) {
                        handleError('Login error: ' . $e->getMessage(), 500);
                    }
                    break;
                    
                    
                case 'logout':
                    try {
                        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
                        $token = str_replace('Bearer ', '', $authHeader);
                        
                        if (!$token) {
                            handleError('Token is required', 400);
                        }
                        
                        $result = $authService->logout($token);
                        
                        if ($result) {
                            sendSuccess(['message' => 'Logged out successfully']);
                        } else {
                            handleError('Failed to logout', 400);
                        }
                    } catch (Exception $e) {
                        handleError('Logout error: ' . $e->getMessage(), 500);
                    }
                    break;
                    
                default:
                    handleError('POST endpoint not found', 404);
                    break;
            }
            break;
            
        case 'GET':
            switch ($path) {
                case 'validate':
                    try {
                        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
                        $token = str_replace('Bearer ', '', $authHeader);
                        
                        if (!$token) {
                            handleError('Token is required', 401);
                        }
                        
                        $user = $authService->validateSession($token);
                        
                        if ($user) {
                            sendSuccess([
                                'user' => [
                                    'id' => $user->id,
                                    'username' => $user->username,
                                    'email' => $user->email
                                ]
                            ]);
                        } else {
                            handleError('Invalid or expired token', 401);
                        }
                    } catch (Exception $e) {
                        handleError('Token validation error: ' . $e->getMessage(), 500);
                    }
                    break;
                    
                default:
                    handleError('Endpoint not found', 404);
                    break;
            }
            break;
            
        default:
            handleError('Method not allowed', 405);
            break;
    }
} catch (Exception $e) {
    handleError('Unexpected error: ' . $e->getMessage(), 500);
}

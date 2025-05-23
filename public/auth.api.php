<?php

use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;
use SoftplanTasksApi\Infrastructure\Repository\PdoUserRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoSessionRepository;
use SoftplanTasksApi\Application\Service\AuthService;

require_once '../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
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

// Validate database connection
if (!$pdoConnection) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed. Please check your database configuration.'
    ]);
    exit();
}

// Test the connection with a simple query
try {
    $pdoConnection->query('SELECT 1');
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}


$userRepository = new PdoUserRepository($pdoConnection);
$sessionRepository = new PdoSessionRepository($pdoConnection);
$authService = new AuthService($userRepository, $sessionRepository);

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($path) {
                case 'login':
                    if (!isset($input['username']) || !isset($input['password'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Username and password are required']);
                        exit();
                    }
                    
                    $result = $authService->login($input['username'], $input['password']);
                    
                    if ($result) {
                        echo json_encode([
                            'success' => true,
                            'data' => $result
                        ]);
                    } else {
                        http_response_code(401);
                        echo json_encode(['error' => 'Invalid credentials']);
                    }
                    break;
                    
                case 'register':
                    if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Username, email and password are required']);
                        exit();
                    }
                    
                    try {
                        $result = $authService->register($input['username'], $input['email'], $input['password']);
                    } catch (\Exception $e) {
                        http_response_code(400);
                        echo json_encode(['error' => $e->getMessage()]);
                        exit();
                    }
                    

                    if ($result) {
                        http_response_code(201);
                        echo json_encode([
                            'success' => true,
                            'data' => $result
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'User already exists or password is too weak']);
                    }
                    break;
                    
                case 'logout':
                    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
                    $token = str_replace('Bearer ', '', $authHeader);
                    
                    if (!$token) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Token is required']);
                        exit();
                    }
                    
                    $result = $authService->logout($token);
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? 'Logged out successfully' : 'Failed to logout'
                    ]);
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint not found']);
                    break;
            }
            break;
            
        case 'GET':
            switch ($path) {
                case 'validate':
                    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
                    $token = str_replace('Bearer ', '', $authHeader);
                    
                    if (!$token) {
                        http_response_code(401);
                        echo json_encode(['error' => 'Token is required']);
                        exit();
                    }
                    
                    $user = $authService->validateSession($token);
                    
                    if ($user) {
                        echo json_encode([
                            'success' => true,
                            'user' => [
                                'id' => $user->id,
                                'username' => $user->username,
                                'email' => $user->email
                            ]
                        ]);
                    } else {
                        http_response_code(401);
                        echo json_encode(['error' => 'Invalid or expired token']);
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint not found']);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

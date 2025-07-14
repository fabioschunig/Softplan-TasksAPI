<?php

require_once '../vendor/autoload.php';

use SoftplanTasksApi\Infrastructure\Persistence\PdoConnectionCreator;
use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;
use SoftplanTasksApi\Infrastructure\Repository\PdoUserRepository;
use SoftplanTasksApi\Infrastructure\Repository\PdoSessionRepository;
use SoftplanTasksApi\Application\Service\AuthService;
use SoftplanTasksApi\Infrastructure\Middleware\AuthMiddleware;
use SoftplanTasksApi\Infrastructure\Middleware\AuthorizationMiddleware;

// CORS headers
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $config = new ConfigAppEnvFile();
    $config->loadEnv();
    
    $pdo = PdoConnectionCreator::createConnection(
        $config->getHost(),
        $config->getDBName(),
        $config->getUsername(),
        $config->getPassword()
    );
    $userRepository = new PdoUserRepository($pdo);
    $sessionRepository = new PdoSessionRepository($pdo);
    $authService = new AuthService($userRepository, $sessionRepository);

    // Authenticate user
    $user = AuthMiddleware::authenticateStatic($authService);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    // Check if user is admin
    if (!AuthorizationMiddleware::canManageUsers($user)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Admin privileges required.']);
        exit();
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['PATH_INFO'] ?? '';

    switch ($method) {
        case 'GET':
            if (empty($path) || $path === '/') {
                // List all users
                $users = $authService->getAllUsers();
                echo json_encode($users);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;

        case 'POST':
            if (empty($path) || $path === '/') {
                // Create new user
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing required fields: username, email, password']);
                    exit();
                }

                $role = $input['role'] ?? 'user';
                
                // Validate role
                if (!in_array($role, ['admin', 'user'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid role. Must be admin or user']);
                    exit();
                }

                $result = $authService->createUser(
                    $input['username'],
                    $input['email'],
                    $input['password'],
                    $role
                );

                if ($result) {
                    http_response_code(201);
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to create user. Username/email may already exist or password is too weak.']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;

        case 'DELETE':
            if (preg_match('/^\/(\d+)$/', $path, $matches)) {
                $userId = (int)$matches[1];
                
                if ($authService->deleteUser($userId)) {
                    http_response_code(200);
                    echo json_encode(['message' => 'User deleted successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to delete user. Cannot delete admin user.']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
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

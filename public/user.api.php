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
        echo json_encode(['error' => 'Não autorizado']);
        exit();
    }

    // Check if user is admin
    if (!AuthorizationMiddleware::canManageUsers($user)) {
        http_response_code(403);
        echo json_encode(['error' => 'Acesso negado. Privilégios de administrador são necessários.']);
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
                echo json_encode(['error' => 'Endpoint não encontrado']);
            }
            break;

        case 'POST':
            if (empty($path) || $path === '/') {
                // Create new user
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Campos obrigatórios ausentes: nome de usuário, e-mail, senha']);
                    exit();
                }

                $role = $input['role'] ?? 'user';
                
                // Validate role
                if (!in_array($role, ['admin', 'user'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Função inválida. Deve ser admin ou user']);
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
                    echo json_encode(['error' => 'Falha ao criar usuário. O nome de usuário/e-mail pode já existir ou a senha é muito fraca.']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint não encontrado']);
            }
            break;

        case 'DELETE':
            if (preg_match('/^\/(\d+)$/', $path, $matches)) {
                $userId = (int)$matches[1];
                
                if ($authService->deleteUser($userId)) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Usuário excluído com sucesso']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Falha ao excluir usuário. Não é possível excluir um usuário administrador.']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint não encontrado']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}

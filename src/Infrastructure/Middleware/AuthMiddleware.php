<?php

namespace SoftplanTasksApi\Infrastructure\Middleware;

use SoftplanTasksApi\Application\Service\AuthService;
use SoftplanTasksApi\Domain\Model\User;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function authenticate(): User|null
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $this->sendUnauthorizedResponse('Authorization header missing or invalid');
            return null;
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $user = $this->authService->validateSession($token);

        if (!$user) {
            $this->sendUnauthorizedResponse('Invalid or expired token');
            return null;
        }

        return $user;
    }

    public function requireAuth(): User
    {
        $user = $this->authenticate();
        
        if (!$user) {
            exit();
        }

        return $user;
    }

    private function sendUnauthorizedResponse(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
}

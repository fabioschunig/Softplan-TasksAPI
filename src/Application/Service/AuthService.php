<?php

namespace SoftplanTasksApi\Application\Service;

use SoftplanTasksApi\Domain\Repository\UserRepository;
use SoftplanTasksApi\Domain\Model\User;
use SoftplanTasksApi\Domain\Model\UserSession;
use SoftplanTasksApi\Infrastructure\Repository\PdoSessionRepository;

class AuthService
{
    private UserRepository $userRepository;
    private PdoSessionRepository $sessionRepository;

    public function __construct(UserRepository $userRepository, PdoSessionRepository $sessionRepository)
    {
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
    }

    public function login(string $username, string $password): array|null
    {
        $user = $this->userRepository->findByUsername($username);
        
        if (!$user || !$user->verifyPassword($password)) {
            return null;
        }

        // Create session
        $sessionToken = UserSession::generateToken();
        $expiresAt = new \DateTime('+24 hours');
        
        $session = $this->sessionRepository->create($user->id, $sessionToken, $expiresAt);
        
        return [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => $session->sessionToken,
            'expires_at' => $session->expiresAt->format('Y-m-d H:i:s')
        ];
    }

    public function register(string $username, string $email, string $password, string $role = 'user'): array|null
    {
        
        // Validate username length
        if (strlen($username) > 31 || strlen($username) < 3) {
            return null;
        }

        // Check if user already exists
        if ($this->userRepository->findByUsername($username) || $this->userRepository->findByEmail($email)) {
            return null;
        }

        // Validate password strength
        if (!$this->isPasswordStrong($password)) {
            return null;
        }

        $passwordHash = User::hashPassword($password);

        $user = $this->userRepository->create($username, $email, $passwordHash, $role);

        // Auto-login after registration
        return $this->login($username, $password);
    }

    public function logout(string $sessionToken): bool
    {
        return $this->sessionRepository->deleteByToken($sessionToken);
    }

    public function validateSession(string $sessionToken): User|null
    {
        $session = $this->sessionRepository->findByToken($sessionToken);
        
        if (!$session || $session->isExpired()) {
            if ($session) {
                $this->sessionRepository->delete($session->id);
            }
            return null;
        }

        return $this->userRepository->findById($session->userId);
    }

    public function cleanExpiredSessions(): int
    {
        return $this->sessionRepository->deleteExpired();
    }

    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAll();
        $result = [];
        
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'created' => $user->created->format('Y-m-d H:i:s')
            ];
        }
        
        return $result;
    }

    public function createUser(string $username, string $email, string $password, string $role = 'user'): array|null
    {
        // Validate username length
        if (strlen($username) > 31 || strlen($username) < 3) {
            return null;
        }

        // Check if user already exists
        if ($this->userRepository->findByUsername($username) || $this->userRepository->findByEmail($email)) {
            return null;
        }

        // Validate password strength
        if (!$this->isPasswordStrong($password)) {
            return null;
        }

        $passwordHash = User::hashPassword($password);
        $user = $this->userRepository->create($username, $email, $passwordHash, $role);

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'created' => $user->created->format('Y-m-d H:i:s')
        ];
    }

    public function deleteUser(int $userId): bool
    {
        // Prevent deletion of admin user
        $user = $this->userRepository->findById($userId);
        if ($user && $user->isAdmin()) {
            return false;
        }
        
        return $this->userRepository->delete($userId);
    }

    private function isPasswordStrong(string $password): bool
    {
        // Minimum 4 characters, at least one letter and one number
        return strlen($password) >= 4 && 
               preg_match('/[A-Za-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
}

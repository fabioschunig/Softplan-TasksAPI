<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Repository\UserRepository;
use SoftplanTasksApi\Domain\Model\User;
use PDO;
use DateTime;

class PdoUserRepository implements UserRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findByUsername(string $username): User|null
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            return null;
        }
        
        return $this->mapToUser($userData);
    }

    public function findByEmail(string $email): User|null
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            return null;
        }
        
        return $this->mapToUser($userData);
    }

    public function findById(int $id): User|null
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            return null;
        }
        
        return $this->mapToUser($userData);
    }

    public function create(string $username, string $email, string $passwordHash, string $role = 'user'): User
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO user (username, email, password_hash, role, created) VALUES (:username, :email, :password_hash, :role, NOW())"
        );
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':role', $role);
        
        $stmt->execute();
        
        $userId = $this->connection->lastInsertId();
        
        return $this->findById((int)$userId);
    }

    public function update(User $user): bool
    {
        $stmt = $this->connection->prepare(
            "UPDATE user SET username = :username, email = :email, password_hash = :password_hash, role = :role, updated = NOW() WHERE id = :id"
        );
        
        $stmt->bindParam(':id', $user->id);
        $stmt->bindParam(':username', $user->username);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':password_hash', $user->passwordHash);
        $stmt->bindParam(':role', $user->role);
        
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM user WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM user ORDER BY created DESC");
        $stmt->execute();
        
        $users = [];
        while ($userData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $this->mapToUser($userData);
        }
        
        return $users;
    }

    private function mapToUser(array $userData): User
    {
        return new User(
            $userData['id'],
            $userData['username'],
            $userData['email'],
            $userData['password_hash'],
            $userData['role'],
            new DateTime($userData['created']),
            $userData['updated'] ? new DateTime($userData['updated']) : null
        );
    }
}

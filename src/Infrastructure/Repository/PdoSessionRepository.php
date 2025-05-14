<?php

namespace SoftplanTasksApi\Infrastructure\Repository;

use SoftplanTasksApi\Domain\Model\UserSession;
use PDO;
use DateTime;

class PdoSessionRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(int $userId, string $sessionToken, DateTime $expiresAt): UserSession
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO user_sessions (user_id, session_token, expires_at, created) VALUES (:user_id, :session_token, :expires_at, NOW())"
        );
        
        $expiresAtFormatted = $expiresAt->format('Y-m-d H:i:s');
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':session_token', $sessionToken);
        $stmt->bindParam(':expires_at', $expiresAtFormatted);
        
        $stmt->execute();
        
        $sessionId = $this->connection->lastInsertId();
        
        return $this->findById((int)$sessionId);
    }

    public function findByToken(string $sessionToken): UserSession|null
    {
        $stmt = $this->connection->prepare("SELECT * FROM user_sessions WHERE session_token = :session_token");
        $stmt->bindParam(':session_token', $sessionToken);
        $stmt->execute();
        
        $sessionData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sessionData) {
            return null;
        }
        
        return $this->mapToUserSession($sessionData);
    }

    public function findById(int $id): UserSession|null
    {
        $stmt = $this->connection->prepare("SELECT * FROM user_sessions WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $sessionData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sessionData) {
            return null;
        }
        
        return $this->mapToUserSession($sessionData);
    }

    public function deleteByToken(string $sessionToken): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM user_sessions WHERE session_token = :session_token");
        $stmt->bindParam(':session_token', $sessionToken);
        
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM user_sessions WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function deleteExpired(): int
    {
        $stmt = $this->connection->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    private function mapToUserSession(array $sessionData): UserSession
    {
        return new UserSession(
            $sessionData['id'],
            $sessionData['user_id'],
            $sessionData['session_token'],
            new DateTime($sessionData['expires_at']),
            new DateTime($sessionData['created'])
        );
    }
}

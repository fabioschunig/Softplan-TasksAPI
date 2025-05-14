<?php

namespace SoftplanTasksApi\Domain\Model;

class UserSession
{
    public readonly int $id;
    public readonly int $userId;
    public readonly string $sessionToken;
    public readonly \DateTime $expiresAt;
    public readonly \DateTime $created;

    public function __construct(
        int $id,
        int $userId,
        string $sessionToken,
        \DateTime $expiresAt,
        \DateTime $created,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->sessionToken = $sessionToken;
        $this->expiresAt = $expiresAt;
        $this->created = $created;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTime();
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

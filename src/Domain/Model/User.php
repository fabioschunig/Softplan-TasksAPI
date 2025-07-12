<?php

namespace SoftplanTasksApi\Domain\Model;

class User
{
    public readonly int $id;
    public readonly string $username;
    public readonly string $email;
    public readonly string $passwordHash;
    public readonly string $role;
    public readonly \DateTime $created;
    public readonly \DateTime|null $updated;

    public function __construct(
        int $id,
        string $username,
        string $email,
        string $passwordHash,
        string $role,
        \DateTime $created,
        \DateTime|null $updated = null,
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->created = $created;
        $this->updated = $updated;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}

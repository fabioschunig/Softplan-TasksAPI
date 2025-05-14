<?php

namespace SoftplanTasksApi\Domain\Repository;

use SoftplanTasksApi\Domain\Model\User;

interface UserRepository
{
    public function findByUsername(string $username): User|null;
    public function findByEmail(string $email): User|null;
    public function findById(int $id): User|null;
    public function create(string $username, string $email, string $passwordHash): User;
    public function update(User $user): bool;
    public function delete(int $id): bool;
}

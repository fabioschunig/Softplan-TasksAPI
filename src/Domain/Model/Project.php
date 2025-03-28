<?php

namespace SoftplanTasksApi\Domain\Model;

class Project
{
    public readonly int $id;
    public readonly string $description;

    public function __construct(
        int $id,
        string $description,
    ) {
        $this->id = $id;
        $this->description = $description;
    }
}

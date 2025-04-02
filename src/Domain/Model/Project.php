<?php

namespace SoftplanTasksApi\Domain\Model;

class Project
{
    public readonly int $id;
    public readonly string $description;
    public readonly \DateTime|null $created;
    public readonly \DateTime|null $updated;

    public function __construct(
        int $id,
        string $description,
        \DateTime|null $created,
        \DateTime|null $updated,
    ) {
        $this->id = $id;
        $this->description = $description;
        $this->created = $created;
        $this->updated = $updated;
    }
}

<?php

namespace SoftplanTasksApi\Domain\Model;

class Task
{
    public readonly int $id;
    public readonly string $description;
    public readonly string|null $tags;
    public readonly int|null $projectId;
    public readonly \DateTime|null $started;
    public readonly \DateTime|null $finished;
    public readonly int $status;
    public readonly \DateTime|null $created;
    public readonly \DateTime|null $updated;

    public function __construct(
        int $id,
        string $description,
        string|null $tags,
        int|null $projectId,
        \DateTime|null $started,
        \DateTime|null $finished,
        int $status,
        \DateTime|null $created,
        \DateTime|null $updated,
    ) {
        $this->id = $id;
        $this->description = $description;
        $this->tags = $tags;
        $this->projectId = $projectId;
        $this->started = $started;
        $this->finished = $finished;
        $this->status = $status;
        $this->created = $created;
        $this->updated = $updated;
    }
}

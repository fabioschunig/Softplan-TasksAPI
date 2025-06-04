<?php

namespace SoftplanTasksApi\Domain\Model;

class Task implements \JsonSerializable
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'tags' => $this->tags,
            'project_id' => $this->projectId,
            'started' => $this->started?->format('Y-m-d H:i:s'),
            'finished' => $this->finished?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'created' => $this->created?->format('Y-m-d H:i:s'),
            'updated' => $this->updated?->format('Y-m-d H:i:s'),
        ];
    }
}

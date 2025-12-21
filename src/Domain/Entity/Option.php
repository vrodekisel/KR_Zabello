<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class Option
{
    private ?int $id;
    private int $pollId;
    private string $labelKey;
    private string $value;
    private int $sortOrder;
    private bool $isActive;

    public function __construct(
        ?int $id,
        int $pollId,
        string $labelKey,
        string $value,
        int $sortOrder,
        bool $isActive
    ) {
        $this->id = $id;
        $this->pollId = $pollId;
        $this->labelKey = $labelKey;
        $this->value = $value;
        $this->sortOrder = $sortOrder;
        $this->isActive = $isActive;
    }

    public static function fromArray(array $row): self
    {
        $id = isset($row['id']) ? (int) $row['id'] : null;

        $pollId   = isset($row['poll_id']) ? (int) $row['poll_id'] : 0;
        $labelKey = (string) ($row['label'] ?? '');
        $value    = (string) ($row['value'] ?? '');
        $sortOrder = isset($row['position']) ? (int) $row['position'] : 1;

        $isActive = true;

        return new self(
            $id,
            $pollId,
            $labelKey,
            $value,
            $sortOrder,
            $isActive
        );
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'poll_id'  => $this->pollId,
            'label'    => $this->labelKey,
            'value'    => $this->value,
            'position' => $this->sortOrder,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPollId(): int
    {
        return $this->pollId;
    }

    public function getLabelKey(): string
    {
        return $this->labelKey;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function belongsToPoll(Poll $poll): bool
    {
        return $this->pollId === $poll->getId();
    }
}

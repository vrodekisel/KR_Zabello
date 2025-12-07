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

    /**
     * Сборка Option из строки таблицы options.
     *
     * Схема options:
     *  id, poll_id, label, value, position, created_at
     *
     * created_at и признак активности в домене не храним,
     * поэтому isActive считаем true по умолчанию.
     */
    public static function fromArray(array $row): self
    {
        $id = isset($row['id']) ? (int) $row['id'] : null;

        $pollId   = isset($row['poll_id']) ? (int) $row['poll_id'] : 0;
        $labelKey = (string) ($row['label'] ?? '');
        $value    = (string) ($row['value'] ?? '');
        $sortOrder = isset($row['position']) ? (int) $row['position'] : 1;

        // В текущей схеме options нет колонки is_active, поэтому считаем все варианты активными.
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

    /**
     * Представление Option в виде массива для INSERT/UPDATE в options.
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'poll_id'  => $this->pollId,
            'label'    => $this->labelKey,
            'value'    => $this->value,
            'position' => $this->sortOrder,
            // created_at можно не задавать — БД проставит по умолчанию
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

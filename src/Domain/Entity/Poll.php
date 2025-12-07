<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

class Poll
{
    public const CONTENT_TYPE_MAP = 'map';
    public const CONTENT_TYPE_MOD = 'mod';

    public const STATUS_DRAFT  = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    private ?int $id;
    private string $contentType;
    private int $contentId;
    private string $titleKey;
    private ?string $descriptionKey;
    private bool $isMultipleChoice;
    private string $status;
    private ?DateTimeImmutable $startsAt;
    private ?DateTimeImmutable $endsAt;
    private int $createdByUserId;
    private DateTimeImmutable $createdAt;

    public function __construct(
        ?int $id,
        string $contentType,
        int $contentId,
        string $titleKey,
        ?string $descriptionKey,
        bool $isMultipleChoice,
        string $status,
        ?DateTimeImmutable $startsAt,
        ?DateTimeImmutable $endsAt,
        int $createdByUserId,
        DateTimeImmutable $createdAt
    ) {
        $this->assertValidContentType($contentType);
        $this->assertValidStatus($status);

        $this->id = $id;
        $this->contentType = $contentType;
        $this->contentId = $contentId;
        $this->titleKey = $titleKey;
        $this->descriptionKey = $descriptionKey;
        $this->isMultipleChoice = $isMultipleChoice;
        $this->status = $status;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->createdByUserId = $createdByUserId;
        $this->createdAt = $createdAt;
    }

    private function assertValidContentType(string $contentType): void
    {
        if (!\in_array($contentType, [self::CONTENT_TYPE_MAP, self::CONTENT_TYPE_MOD], true)) {
            throw new \InvalidArgumentException('Invalid content type');
        }
    }

    private function assertValidStatus(string $status): void
    {
        if (!\in_array($status, [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_CLOSED], true)) {
            throw new \InvalidArgumentException('Invalid poll status');
        }
    }

    /**
     * Сборка Poll из строки таблицы polls.
     *
     * Схема polls (schema.sql):
     *  id, title, description, type, is_active, content_type, content_key,
     *  created_by, created_at, expires_at
     *
     * Мы мапим:
     *  - title/description как ключи локализации titleKey/descriptionKey;
     *  - content_type -> contentType;
     *  - content_key (строка) -> contentId (int, если возможно, иначе 0);
     *  - type: 'single'/'multiple' -> isMultipleChoice;
     *  - is_active -> status (active/closed);
     *  - expires_at -> endsAt;
     *  - startsAt в текущей схеме нет — оставляем null.
     */
    public static function fromArray(array $row): self
    {
        $id = isset($row['id']) ? (int) $row['id'] : null;

        $titleKey       = (string) ($row['title'] ?? '');
        $descriptionKey = isset($row['description'])
            ? (string) $row['description']
            : null;

        $contentType = isset($row['content_type']) && $row['content_type'] !== ''
            ? (string) $row['content_type']
            : self::CONTENT_TYPE_MAP;

        // content_key хранится как строковый ключ; пытаемся привести к int,
        // если это не число — сохраняем 0 (минимум, не падаем).
        $contentKeyRaw = $row['content_key'] ?? null;
        $contentId = 0;
        if ($contentKeyRaw !== null && $contentKeyRaw !== '') {
            $contentId = \ctype_digit((string) $contentKeyRaw)
                ? (int) $contentKeyRaw
                : 0;
        }

        $typeRaw = $row['type'] ?? 'single';
        $isMultipleChoice = ($typeRaw === 'multiple');

        $isActiveRaw = isset($row['is_active']) ? (int) $row['is_active'] : 0;
        $status = $isActiveRaw === 1 ? self::STATUS_ACTIVE : self::STATUS_CLOSED;

        $startsAt = null;

        $endsAtRaw = $row['expires_at'] ?? null;
        $endsAt = $endsAtRaw
            ? new DateTimeImmutable((string) $endsAtRaw)
            : null;

        $createdByUserId = isset($row['created_by']) ? (int) $row['created_by'] : 0;

        $createdAtRaw = $row['created_at'] ?? null;
        $createdAt = $createdAtRaw
            ? new DateTimeImmutable((string) $createdAtRaw)
            : new DateTimeImmutable('now');

        return new self(
            $id,
            $contentType,
            $contentId,
            $titleKey,
            $descriptionKey,
            $isMultipleChoice,
            $status,
            $startsAt,
            $endsAt,
            $createdByUserId,
            $createdAt
        );
    }

    /**
     * Представление Poll в виде массива для INSERT/UPDATE в polls.
     *
     * Обратное отображение к fromArray:
     *  - titleKey -> title
     *  - descriptionKey -> description
     *  - contentType -> content_type
     *  - contentId -> content_key (строка)
     *  - isMultipleChoice -> type ('single'|'multiple')
     *  - status -> is_active (1 только для active)
     *  - endsAt -> expires_at
     *  - createdByUserId -> created_by
     *  - createdAt -> created_at
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->titleKey,
            'description'  => $this->descriptionKey,
            'type'         => $this->isMultipleChoice ? 'multiple' : 'single',
            'is_active'    => $this->status === self::STATUS_ACTIVE ? 1 : 0,
            'content_type' => $this->contentType,
            'content_key'  => (string) $this->contentId,
            'created_by'   => $this->createdByUserId,
            'created_at'   => $this->createdAt->format('Y-m-d H:i:s'),
            'expires_at'   => $this->endsAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getContentId(): int
    {
        return $this->contentId;
    }

    public function getTitleKey(): string
    {
        return $this->titleKey;
    }

    public function getDescriptionKey(): ?string
    {
        return $this->descriptionKey;
    }

    public function isMultipleChoice(): bool
    {
        return $this->isMultipleChoice;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartsAt(): ?DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getCreatedByUserId(): int
    {
        return $this->createdByUserId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isActive(DateTimeImmutable $now): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->startsAt !== null && $now < $this->startsAt) {
            return false;
        }

        if ($this->endsAt !== null && $now > $this->endsAt) {
            return false;
        }

        return true;
    }

    public function close(): void
    {
        $this->status = self::STATUS_CLOSED;
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
    }
}

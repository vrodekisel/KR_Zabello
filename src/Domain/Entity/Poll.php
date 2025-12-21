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
    private ?string $contextKey;
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
        ?string $contextKey,
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

        $this->id               = $id;
        $this->contentType      = $contentType;
        $this->contextKey       = $contextKey;
        $this->titleKey         = $titleKey;
        $this->descriptionKey   = $descriptionKey;
        $this->isMultipleChoice = $isMultipleChoice;
        $this->status           = $status;
        $this->startsAt         = $startsAt;
        $this->endsAt           = $endsAt;
        $this->createdByUserId  = $createdByUserId;
        $this->createdAt        = $createdAt;
    }

    private function assertValidContentType(string $contentType): void
    {

    }

    private function assertValidStatus(string $status): void
    {
        if (!\in_array($status, [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_CLOSED], true)) {
            throw new \InvalidArgumentException('Invalid poll status');
        }
    }


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

        $contextKey = isset($row['content_key']) && $row['content_key'] !== ''
            ? (string) $row['content_key']
            : null;

        $typeRaw          = $row['type'] ?? 'single';
        $isMultipleChoice = ($typeRaw === 'multiple');

        $isActiveRaw = isset($row['is_active']) ? (int) $row['is_active'] : 0;
        $status      = $isActiveRaw === 1 ? self::STATUS_ACTIVE : self::STATUS_CLOSED;

        $startsAt = null;

        $endsAtRaw = $row['expires_at'] ?? null;
        $endsAt    = $endsAtRaw
            ? new DateTimeImmutable((string) $endsAtRaw)
            : null;

        $createdByUserId = isset($row['created_by']) ? (int) $row['created_by'] : 0;

        $createdAtRaw = $row['created_at'] ?? null;
        $createdAt    = $createdAtRaw
            ? new DateTimeImmutable((string) $createdAtRaw)
            : new DateTimeImmutable('now');

        return new self(
            $id,
            $contentType,
            $contextKey,
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

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->titleKey,
            'description'  => $this->descriptionKey,
            'type'         => $this->isMultipleChoice ? 'multiple' : 'single',
            'is_active'    => $this->status === self::STATUS_ACTIVE ? 1 : 0,
            'content_type' => $this->contentType,
            'content_key'  => $this->contextKey,
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
        if ($this->contextKey !== null && ctype_digit($this->contextKey)) {
            return (int) $this->contextKey;
        }

        return 0;
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

    public function getContextType(): string
    {
        return $this->contentType;
    }

    public function getContextKey(): ?string
    {
        return $this->contextKey;
    }
}

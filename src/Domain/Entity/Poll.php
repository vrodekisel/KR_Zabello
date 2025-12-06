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

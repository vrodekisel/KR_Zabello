<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

class Vote
{
    private ?int $id;
    private int $pollId;
    private int $optionId;
    private int $userId;
    private DateTimeImmutable $createdAt;
    private ?string $ipAddress;
    private ?string $userAgent;
    private ?string $contextKey;

    public function __construct(
        ?int $id,
        int $pollId,
        int $optionId,
        int $userId,
        DateTimeImmutable $createdAt,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $contextKey
    ) {
        $this->id = $id;
        $this->pollId = $pollId;
        $this->optionId = $optionId;
        $this->userId = $userId;
        $this->createdAt = $createdAt;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->contextKey = $contextKey;
    }

    public static function createNew(
        int $pollId,
        int $optionId,
        int $userId,
        DateTimeImmutable $createdAt,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $contextKey
    ): self {
        return new self(
            null,
            $pollId,
            $optionId,
            $userId,
            $createdAt,
            $ipAddress,
            $userAgent,
            $contextKey
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPollId(): int
    {
        return $this->pollId;
    }

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getContextKey(): ?string
    {
        return $this->contextKey;
    }
}

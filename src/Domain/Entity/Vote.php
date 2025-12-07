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

    /**
     * Сборка Vote из строки таблицы votes.
     *
     * Схема votes:
     *  id, poll_id, option_id, user_id, created_at
     *
     * IP/UA/контекст в основном хранятся в vote_logs, поэтому
     * здесь они будут null.
     */
    public static function fromArray(array $row): self
    {
        $id = isset($row['id']) ? (int) $row['id'] : null;

        $pollId   = isset($row['poll_id']) ? (int) $row['poll_id'] : 0;
        $optionId = isset($row['option_id']) ? (int) $row['option_id'] : 0;
        $userId   = isset($row['user_id']) ? (int) $row['user_id'] : 0;

        $createdAtRaw = $row['created_at'] ?? null;
        $createdAt = $createdAtRaw
            ? new DateTimeImmutable((string) $createdAtRaw)
            : new DateTimeImmutable('now');

        return new self(
            $id,
            $pollId,
            $optionId,
            $userId,
            $createdAt,
            null, // ipAddress — не из таблицы votes
            null, // userAgent — не из таблицы votes
            null  // contextKey — не из таблицы votes
        );
    }

    /**
     * Представление Vote в виде массива для INSERT/UPDATE в votes.
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'poll_id'    => $this->pollId,
            'option_id'  => $this->optionId,
            'user_id'    => $this->userId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
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

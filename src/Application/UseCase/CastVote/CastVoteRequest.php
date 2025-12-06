<?php

declare(strict_types=1);

namespace App\Application\UseCase\CastVote;

final class CastVoteRequest
{
    private int $userId;
    private int $pollId;
    private int $optionId;
    private string $ipAddress;
    private ?string $userAgent;

    public function __construct(
        int $userId,
        int $pollId,
        int $optionId,
        string $ipAddress,
        ?string $userAgent
    ) {
        $this->userId = $userId;
        $this->pollId = $pollId;
        $this->optionId = $optionId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPollId(): int
    {
        return $this->pollId;
    }

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\UseCase\CastVote;

final class CastVoteResponse
{
    private bool $success;
    private ?string $messageKey;

    public function __construct(bool $success, ?string $messageKey = null)
    {
        $this->success = $success;
        $this->messageKey = $messageKey;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessageKey(): ?string
    {
        return $this->messageKey;
    }
}

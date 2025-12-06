<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetPollResults;

final class GetPollResultsRequest
{
    private int $pollId;

    public function __construct(int $pollId)
    {
        $this->pollId = $pollId;
    }

    public function getPollId(): int
    {
        return $this->pollId;
    }
}

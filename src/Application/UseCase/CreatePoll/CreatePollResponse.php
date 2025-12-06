<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreatePoll;

use App\Application\DTO\PollDTO;

final class CreatePollResponse
{
    private PollDTO $poll;

    public function __construct(PollDTO $poll)
    {
        $this->poll = $poll;
    }

    public function getPoll(): PollDTO
    {
        return $this->poll;
    }
}

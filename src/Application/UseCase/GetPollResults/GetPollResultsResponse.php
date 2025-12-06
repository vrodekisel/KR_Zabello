<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetPollResults;

use App\Application\DTO\PollDTO;

final class GetPollResultsResponse
{
    private PollDTO $poll;
    /** @var array<int,int> optionId => totalVotes */
    private array $results;

    /**
     * @param array<int,int> $results
     */
    public function __construct(PollDTO $poll, array $results)
    {
        $this->poll = $poll;
        $this->results = $results;
    }

    public function getPoll(): PollDTO
    {
        return $this->poll;
    }

    /**
     * @return array<int,int>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}

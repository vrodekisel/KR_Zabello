<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetPollResults;

use App\Application\DTO\PollDTO;

final class GetPollResultsResponse
{
    private PollDTO $poll;

    /**
     *
     * @var array<int,int>
     */
    private array $results;

    /**
     */
    private int $totalVotes;

    /**
     *
     * @var array<int,float>
     */
    private array $percentages;

    /**
     * @param array<int,int> $results
     */
    public function __construct(PollDTO $poll, array $results)
    {
        $this->poll    = $poll;
        $this->results = $results;

        $this->totalVotes = array_sum($results);

        $percentages = [];
        if ($this->totalVotes > 0) {
            foreach ($results as $optionId => $count) {
                $percentages[$optionId] = round(($count / $this->totalVotes) * 100, 2);
            }
        }

        $this->percentages = $percentages;
    }

    public function getPoll(): PollDTO
    {
        return $this->poll;
    }

    /**
     *
     * @return array<int,int>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     */
    public function getTotalVotes(): int
    {
        return $this->totalVotes;
    }

    /**
     *
     * @return array<int,float>
     */
    public function getPercentages(): array
    {
        return $this->percentages;
    }
}

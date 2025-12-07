<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetPollResults;

use App\Application\DTO\PollDTO;

final class GetPollResultsResponse
{
    private PollDTO $poll;

    /**
     * Сырые результаты: optionId => totalVotes.
     *
     * @var array<int,int>
     */
    private array $results;

    /**
     * Общее количество голосов по опросу.
     */
    private int $totalVotes;

    /**
     * Проценты по каждому варианту: optionId => percent (0–100).
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
                // round до двух знаков после запятой, чтобы было аккуратно
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
     * Сырые результаты: optionId => totalVotes.
     *
     * @return array<int,int>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Общее количество голосов.
     */
    public function getTotalVotes(): int
    {
        return $this->totalVotes;
    }

    /**
     * Проценты по каждому варианту: optionId => percent (0–100).
     *
     * @return array<int,float>
     */
    public function getPercentages(): array
    {
        return $this->percentages;
    }
}

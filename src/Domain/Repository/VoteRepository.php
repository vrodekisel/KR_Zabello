<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Vote;
use DateTimeImmutable;

interface VoteRepository
{
    public function findByUserAndPoll(int $userId, int $pollId): ?Vote;

    public function add(Vote $vote): void;

    /**
     * @return array<int,int>  key: optionId, value: votes count
     */
    public function countByPollGroupedByOption(int $pollId): array;

    public function countRecentVotesByUser(int $userId, DateTimeImmutable $since): int;
}

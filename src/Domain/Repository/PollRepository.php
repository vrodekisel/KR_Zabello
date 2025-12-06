<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Poll;
use App\Domain\Entity\Option;
use DateTimeImmutable;

interface PollRepository
{
    public function findById(int $id): ?Poll;

    public function findActiveById(int $id, DateTimeImmutable $now): ?Poll;

    /**
     * @return Poll[]
     */
    public function findAllActiveByContent(
        string $contentType,
        int $contentId,
        DateTimeImmutable $now
    ): array;

    public function add(Poll $poll, array $options): void;

    public function save(Poll $poll): void;

    /**
     * @return Option[]
     */
    public function findOptionsByPollId(int $pollId): array;
}

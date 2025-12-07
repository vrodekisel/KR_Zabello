<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Entity\Poll;
use App\Domain\Entity\Vote;
use DateTimeImmutable;

class VotePolicyService
{
    private int $maxVotesPerInterval;

    public function __construct(int $maxVotesPerInterval = 10)
    {
        $this->maxVotesPerInterval = $maxVotesPerInterval;
    }

    /**
     * Высокоуровневая проверка, которую можно вызывать из прикладного слоя.
     *
     * @return array{allowed: bool, reasonCode: ?string}
     */
    public function canUserVote(
        User $user,
        Poll $poll,
        DateTimeImmutable $now,
        ?Vote $existingVote,
        int $recentVotesCount
    ): array {
        if ($user->isBanned()) {
            return [
                'allowed'    => false,
                'reasonCode' => 'error.user_banned',
            ];
        }

        if (!$poll->isActive($now)) {
            return [
                'allowed'    => false,
                'reasonCode' => 'error.poll_not_active',
            ];
        }

        if ($existingVote !== null) {
            return [
                'allowed'    => false,
                'reasonCode' => 'error.already_voted',
            ];
        }

        if ($recentVotesCount >= $this->maxVotesPerInterval) {
            return [
                'allowed'    => false,
                'reasonCode' => 'error.too_many_votes',
            ];
        }

        return [
            'allowed'    => true,
            'reasonCode' => null,
        ];
    }

    /**
     * Метод, который используют юнит-тесты:
     * проверяет, голосовал ли уже пользователь в этом опросе.
     *
     * @param Vote[] $existingVotes
     *
     * @throws \DomainException если пользователь уже голосовал
     */
    public function assertCanVote(
        Poll $poll,
        int $userId,
        array $existingVotes,
        string $ipAddress,
        string $userAgent
    ): void {
        foreach ($existingVotes as $vote) {
            // Ожидаем, что у Vote есть getPollId() и getUserId()
            if (
                $vote instanceof Vote
                && $vote->getPollId() === $poll->getId()
                && $vote->getUserId() === $userId
            ) {
                throw new \DomainException('vote.error.already_voted');
            }
        }

        // Если дошли досюда — ограничений по "уже голосовал" нет.
        // Остальные проверки (бан, лимит по времени и т.п.)
        // можно делать отдельно через canUserVote(), если нужно.
    }
}

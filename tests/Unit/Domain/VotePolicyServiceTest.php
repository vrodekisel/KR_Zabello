<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Domain\Service\VotePolicyService;
use App\Domain\Entity\Vote;
use App\Domain\Entity\Poll;

final class VotePolicyServiceTest extends TestCase
{
    public function testUserCanVoteIfNoPreviousVotes(): void
    {
        $service = new VotePolicyService();

        $poll = new Poll(
            1,
            Poll::CONTENT_TYPE_MAP,
            42,
            'poll.title.next_map_test',
            'poll.description.next_map_test',
            true,
            Poll::STATUS_ACTIVE,
            new \DateTimeImmutable('2025-01-01T00:00:00Z'),
            null,
            1,
            new \DateTimeImmutable('2024-12-01T00:00:00Z')
        );
        $existingVotes = [];
        $service->assertCanVote(
            $poll,
            10,
            $existingVotes,
            '127.0.0.1',
            'UserAgent/1.0'
        );
        $this->assertTrue(true);
    }
    public function testUserCannotVoteTwiceInSamePoll(): void
    {
        $service = new VotePolicyService();

        $poll = new Poll(
            1,
            Poll::CONTENT_TYPE_MAP,
            42,
            'poll.title.next_map_test',
            'poll.description.next_map_test',
            true,
            Poll::STATUS_ACTIVE,
            new \DateTimeImmutable('2025-01-01T00:00:00Z'),
            null,
            1,
            new \DateTimeImmutable('2024-12-01T00:00:00Z')
        );

        $existingVotes = [
            new Vote(
                1,
                $poll->getId() ?? 1,
                100,
                10,
                new \DateTimeImmutable('2025-01-01T00:05:00Z'),
                '127.0.0.1',
                'UserAgent/1.0',
                null
            ),
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('vote.error.already_voted');

        $service->assertCanVote(
            $poll,
            10,
            $existingVotes,
            '127.0.0.1',
            'UserAgent/1.0'
        );
    }
}

<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Poll;

final class PollTest extends TestCase
{
    public function testPollStoresBasicData(): void
    {
        $now = new \DateTimeImmutable('2025-01-01T00:00:00Z');

        $poll = new Poll(
            null,
            Poll::CONTENT_TYPE_MAP,
            42,
            'poll.title.next_map_test',
            'poll.description.next_map_test',
            false,
            Poll::STATUS_ACTIVE,
            $now,
            null,
            1,
            $now
        );

        $this->assertNull($poll->getId());
        $this->assertSame(Poll::CONTENT_TYPE_MAP, $poll->getContentType());
        $this->assertSame(42, $poll->getContentId());
        $this->assertSame('poll.title.next_map_test', $poll->getTitleKey());
        $this->assertSame('poll.description.next_map_test', $poll->getDescriptionKey());
        $this->assertFalse($poll->isMultipleChoice());
        $this->assertSame(Poll::STATUS_ACTIVE, $poll->getStatus());
        $this->assertSame(1, $poll->getCreatedByUserId());
    }

    public function testPollCanBeActivatedAndClosed(): void
    {
        $now = new \DateTimeImmutable('2025-01-01T00:00:00Z');

        $poll = new Poll(
            null,
            Poll::CONTENT_TYPE_MAP,
            42,
            'poll.title.next_map_test',
            'poll.description.next_map_test',
            false,
            Poll::STATUS_ACTIVE,
            null,
            null,
            1,
            $now
        );

        $this->assertTrue($poll->isActive($now));

        $poll->close();
        $this->assertFalse($poll->isActive($now));

        $poll->activate();
        $this->assertTrue($poll->isActive($now));
    }
}

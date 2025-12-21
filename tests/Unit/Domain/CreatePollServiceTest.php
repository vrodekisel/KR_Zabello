<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Application\UseCase\CreatePoll\CreatePollRequest;
use App\Application\UseCase\CreatePoll\CreatePollResponse;
use App\Domain\Repository\PollRepository;
use App\Domain\Repository\UserRepository;
use App\Domain\Entity\Poll;
use App\Domain\Entity\Option;
use App\Domain\Entity\User;

final class CreatePollServiceTest extends TestCase
{
    public function testCreatePollPersistsPollAndReturnsResponse(): void
    {
        $pollRepository = new InMemoryPollRepositoryForCreatePollService();
        $userRepository = $this->createMock(UserRepository::class);
        $user = $this->createMock(User::class);
        if (method_exists($user, 'isBanned')) {
            $user->method('isBanned')->willReturn(false);
        }
        $userRepository
            ->method('findById')
            ->willReturn($user);
        $service = new CreatePollService($pollRepository, $userRepository);
        $request = new CreatePollRequest(
            1,
            'poll.title.next_map_test',
            'poll.description.next_map_test',
            Poll::CONTENT_TYPE_MAP,
            [
                'option.label.map.ancient_forest',
                'option.label.map.crystal_cavern',
            ],
            null
        );
        $response = $service->handle($request);
        $this->assertInstanceOf(CreatePollResponse::class, $response);
        $allPolls = $pollRepository->getAll();
        $this->assertCount(1, $allPolls);
        /** @var Poll $savedPoll */
        $savedPoll = $allPolls[0];
        $this->assertSame('poll.title.next_map_test', $savedPoll->getTitleKey());
        $this->assertSame('poll.description.next_map_test', $savedPoll->getDescriptionKey());
        $this->assertSame(Poll::CONTENT_TYPE_MAP, $savedPoll->getContentType());
    }
}

/**
 */
final class InMemoryPollRepositoryForCreatePollService implements PollRepository
{
    /** @var array<int, Poll> */
    private array $polls = [];

    /** @var array<int, Option[]> */
    private array $optionsByPoll = [];

    private int $autoIncrement = 1;

    /**
     *
     * @return Poll[]
     */
    public function getAll(): array
    {
        return array_values($this->polls);
    }

    public function findById(int $id): ?Poll
    {
        return $this->polls[$id] ?? null;
    }

    public function findActiveById(int $id, \DateTimeImmutable $now): ?Poll
    {
        return $this->polls[$id] ?? null;
    }

    /**
     * @return Poll[]
     */
    public function findAllActiveByContent(
        string $contentType,
        int $contentId,
        \DateTimeImmutable $now
    ): array {
        return array_values($this->polls);
    }

    /**
     * @return Poll[]
     */
    public function findAllActive(\DateTimeImmutable $now): array
    {
        return array_values($this->polls);
    }

    /**
     * @return Poll[]
     */
    public function findRecentByContent(
        int $limit,
        string $contentType,
        int $contentId,
        \DateTimeImmutable $now
    ): array {
        $filtered = array_filter(
            $this->polls,
            static function (Poll $poll) use ($contentType, $contentId): bool {
                return $poll->getContentType() === $contentType
                    && $poll->getContentId() === $contentId;
            }
        );

        return array_slice(array_values($filtered), 0, $limit);
    }

    /**
     * @return Poll[]
     */
    public function findByStatusAndType(
        string $status,
        string $contentType,
        \DateTimeImmutable $now
    ): array {
        $filtered = array_filter(
            $this->polls,
            static function (Poll $poll) use ($status, $contentType): bool {
                return $poll->getStatus() === $status
                    && $poll->getContentType() === $contentType;
            }
        );

        return array_values($filtered);
    }

    public function add(Poll $poll, array $options): void
    {
        $this->storePoll($poll, $options);
    }

    public function save(Poll $poll): void
    {
        $id = $poll->getId();
        $options = $id !== null ? ($this->optionsByPoll[$id] ?? []) : [];
        $this->storePoll($poll, $options);
    }

    /**
     * @return Option[]
     */
    public function findOptionsByPollId(int $pollId): array
    {
        return $this->optionsByPoll[$pollId] ?? [];
    }

    /**
     *
     * @param Option[] $options
     */
    private function storePoll(Poll $poll, array $options): void
    {
        $id = $poll->getId();
        if ($id === null) {
            $id = $this->autoIncrement++;

            $ref = new \ReflectionClass($poll);
            if ($ref->hasProperty('id')) {
                $prop = $ref->getProperty('id');
                $prop->setAccessible(true);
                $prop->setValue($poll, $id);
            }
        }

        $this->polls[$id]         = $poll;
        $this->optionsByPoll[$id] = $options;
    }
}

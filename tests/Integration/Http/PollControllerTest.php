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

/**
 * Интеграционный тест: проверяем, что CreatePollService
 * корректно работает поверх реализаций репозиториев.
 *
 * Класс называется PollControllerTest, потому что лежит в
 * tests/Integration/Http, но мы здесь тестируем сервис
 * с in-memory репозиторием, не HTTP.
 */
final class PollControllerTest extends TestCase
{
    public function testCreatePollThroughService(): void
    {
        $pollRepository = new InMemoryPollRepositoryForHttp();

        $userRepository = $this->createMock(UserRepository::class);
        $user = $this->createMock(User::class);
        if (method_exists($user, 'isBanned')) {
            $user->method('isBanned')->willReturn(false);
        }
        $userRepository
            ->method('findById')
            ->willReturn($user);

        $createPollService = new CreatePollService($pollRepository, $userRepository);

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

        $response = $createPollService->handle($request);

        $this->assertInstanceOf(CreatePollResponse::class, $response);

        $allPolls = $pollRepository->getAll();
        $this->assertCount(1, $allPolls);

        /** @var Poll $savedPoll */
        $savedPoll = $allPolls[0];

        $this->assertInstanceOf(Poll::class, $savedPoll);
        $this->assertSame('poll.title.next_map_test', $savedPoll->getTitleKey());
        $this->assertSame('poll.description.next_map_test', $savedPoll->getDescriptionKey());
        $this->assertSame(Poll::CONTENT_TYPE_MAP, $savedPoll->getContentType());
    }
}

/**
 * In-memory реализация PollRepository для интеграционного теста HTTP-слоя.
 * Реализует все методы интерфейса, хранит данные в массивах.
 */
final class InMemoryPollRepositoryForHttp implements PollRepository
{
    /** @var array<int, Poll> */
    private array $polls = [];

    /** @var array<int, Option[]> */
    private array $optionsByPoll = [];

    private int $autoIncrement = 1;

    /**
     * Внутренняя логика сохранения опроса и его опций.
     *
     * @param Option[] $options
     */
    private function storePoll(Poll $poll, array $options = []): void
    {
        $id = $poll->getId();

        if ($id === null) {
            $id = $this->autoIncrement++;

            // Проставляем id через reflection, если поле приватное
            $reflection = new \ReflectionObject($poll);
            if ($reflection->hasProperty('id')) {
                $property = $reflection->getProperty('id');
                $property->setAccessible(true);
                $property->setValue($poll, $id);
            }
        }

        $this->polls[$id]         = $poll;
        $this->optionsByPoll[$id] = $options;
    }

    /**
     * Хелпер для теста — вернуть все опросы.
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
        // Для теста считаем, что все сохранённые опросы активные
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
        // Для интеграционного теста этого достаточно
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

    /**
     * @param Option[] $options
     */
    public function add(Poll $poll, array $options): void
    {
        $this->storePoll($poll, $options);
    }

    public function save(Poll $poll): void
    {
        $id      = $poll->getId();
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
}

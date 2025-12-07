<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreatePoll;

use App\Application\DTO\PollDTO;
use App\Domain\Entity\Poll;
use App\Domain\Repository\PollRepository;
use App\Domain\Repository\UserRepository;

final class CreatePollService
{
    private PollRepository $pollRepository;
    private UserRepository $userRepository;

    public function __construct(
        PollRepository $pollRepository,
        UserRepository $userRepository
    ) {
        $this->pollRepository = $pollRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(CreatePollRequest $request): CreatePollResponse
    {
        $creatorId = $request->getCreatorUserId();

        $user = $this->userRepository->findById($creatorId);
        if ($user === null) {
            throw new \RuntimeException('error.user_not_found');
        }

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            throw new \RuntimeException('error.user_banned');
        }

        $now = new \DateTimeImmutable();

        // В текущей модели Poll у нас есть contentType + contentId.
        // ContextType из реквеста — это как раз тип контента (map/mod).
        $contentType = $request->getContextType();

        // Если у реквеста есть getContentId(), используем его.
        // Если нет — подставляем 0, чтобы не падать.
        $contentId = method_exists($request, 'getContentId')
            ? $request->getContentId()
            : 0;

        $poll = new Poll(
            null,                          // id
            $contentType,                  // contentType (map/mod)
            $contentId,                    // contentId (id карты/мода)
            $request->getTitleKey(),       // titleKey
            $request->getDescriptionKey(), // descriptionKey
            false,                         // isMultipleChoice
            Poll::STATUS_ACTIVE,           // status
            $now,                          // startsAt
            $request->getExpiresAt(),      // endsAt (может быть null)
            $creatorId,                    // createdByUserId
            $now                           // createdAt
        );

        // По интерфейсу PollRepository::save ничего не возвращает.
        $this->pollRepository->save($poll);

        return new CreatePollResponse(
            PollDTO::fromEntity($poll)
        );
    }
}

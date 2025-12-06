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
        $user = $this->userRepository->findById($request->getCreatorUserId());

        if ($user === null) {
            throw new \RuntimeException('error.user_not_found');
        }

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            throw new \RuntimeException('error.user_banned');
        }

        // Предполагаем наличие фабричного метода в доменной сущности Poll.
        // Если у тебя другое имя, подстрой под реальную реализацию.
        if (method_exists(Poll::class, 'create')) {
            $poll = Poll::create(
                $request->getTitleKey(),
                $request->getDescriptionKey(),
                $request->getContextType(),
                $request->getOptionLabelKeys(),
                $request->getExpiresAt(),
                $request->getCreatorUserId()
            );
        } else {
            // Фолбэк на конструктор, если фабрики нет.
            $poll = new Poll(
                null,
                $request->getTitleKey(),
                $request->getDescriptionKey(),
                $request->getContextType(),
                $request->getOptionLabelKeys(),
                Poll::STATUS_ACTIVE,
                new \DateTimeImmutable(),
                $request->getExpiresAt(),
                $request->getCreatorUserId()
            );
        }

        $savedPoll = $this->pollRepository->save($poll);

        return new CreatePollResponse(
            PollDTO::fromEntity($savedPoll)
        );
    }
}

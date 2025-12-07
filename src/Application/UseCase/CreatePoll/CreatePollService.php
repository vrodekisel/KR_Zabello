<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreatePoll;

use App\Application\DTO\PollDTO;
use App\Domain\Entity\Poll;
use App\Domain\Entity\User;
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

        $now         = new \DateTimeImmutable();
        $contentType = $request->getContextType();
        $contextKey  = $request->getContextKey();

        $poll = new Poll(
            null,                          // id
            $contentType,                  // contentType (MAP/MOD/...)
            $contextKey,                   // contextKey (next_map, better_grass, ...)
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

    /**
     * Упрощённый фасад для контроллеров: принимает доменного пользователя
     * и "сырые" данные формы/JSON, собирает CreatePollRequest и вызывает handle().
     *
     * @param User                 $user
     * @param array<string, mixed> $data
     */
    public function createPoll(User $user, array $data): CreatePollResponse
    {
        $rawOptions      = $data['options'] ?? [];
        $optionLabelKeys = [];

        // Приводим options к массиву строк-ключей
        if (is_array($rawOptions)) {
            foreach ($rawOptions as $item) {
                // Вариант: ['label_key' => 'option.map_1']
                if (is_array($item) && isset($item['label_key'])) {
                    $optionLabelKeys[] = (string) $item['label_key'];
                    continue;
                }

                // Вариант: просто строка 'option.map_1'
                if (is_string($item)) {
                    $optionLabelKeys[] = $item;
                }
            }
        }

        // 👇 ВАЖНО: поддерживаем и старые названия, и новые.
        $contextType = (string)(
            $data['context_type']
            ?? $data['content_type']
            ?? ''
        );

        $contextKey = (string)(
            $data['context_key']
            ?? $data['content_key']
            ?? ''
        );

        $request = new CreatePollRequest(
            $user->getId() ?? 0,
            (string)($data['title_key'] ?? ''),
            isset($data['description_key']) && $data['description_key'] !== null
                ? (string)$data['description_key']
                : null,
            $contextType,
            $contextKey,
            $optionLabelKeys,
            null // expiresAt
        );

        return $this->handle($request);
    }
}

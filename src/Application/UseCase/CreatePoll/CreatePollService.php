<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreatePoll;

use App\Application\DTO\PollDTO;
use App\Domain\Entity\Poll;
use App\Domain\Entity\User;
use App\Domain\Repository\PollRepository;
use App\Domain\Repository\UserRepository;
use App\Domain\Entity\Option;
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
            null,
            $contentType,
            $contextKey,
            $request->getTitleKey(),
            $request->getDescriptionKey(),
            false,
            Poll::STATUS_ACTIVE,
            $now,
            $request->getExpiresAt(),
            $creatorId,
            $now
        );

        $options          = [];
        $optionLabelKeys  = $request->getOptionLabelKeys();
        $position         = 1;

        foreach ($optionLabelKeys as $labelKey) {
            $labelKey = trim((string) $labelKey);
            if ($labelKey === '') {
                continue;
            }

            $options[] = new Option(
                null,
                0,
                $labelKey,
                $labelKey,
                $position++,
                true
            );
        }

        $this->pollRepository->add($poll, $options);
        
        return new CreatePollResponse(
            PollDTO::fromEntity($poll)
        );
    }


    /**
     *
     * @param User                 $user
     * @param array<string, mixed> $data
     */
    public function createPoll(User $user, array $data): CreatePollResponse
    {
        $rawOptions      = $data['options'] ?? [];
        $optionLabelKeys = [];

        if (is_array($rawOptions)) {
            foreach ($rawOptions as $item) {
                if (is_array($item) && isset($item['label_key'])) {
                    $optionLabelKeys[] = (string) $item['label_key'];
                    continue;
                }

                if (is_string($item)) {
                    $optionLabelKeys[] = $item;
                }
            }
        }

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
            null
        );

        return $this->handle($request);
    }
}

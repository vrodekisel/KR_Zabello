<?php

declare(strict_types=1);

namespace App\Application\UseCase\CastVote;

use App\Domain\Entity\Vote;
use App\Domain\Repository\PollRepository;
use App\Domain\Repository\UserRepository;
use App\Domain\Repository\VoteRepository;
use App\Domain\Service\VotePolicyService;

final class CastVoteService
{
    private PollRepository $pollRepository;
    private UserRepository $userRepository;
    private VoteRepository $voteRepository;
    private VotePolicyService $votePolicyService;

    public function __construct(
        PollRepository $pollRepository,
        UserRepository $userRepository,
        VoteRepository $voteRepository,
        VotePolicyService $votePolicyService
    ) {
        $this->pollRepository     = $pollRepository;
        $this->userRepository     = $userRepository;
        $this->voteRepository     = $voteRepository;
        $this->votePolicyService  = $votePolicyService;
    }

    public function handle(CastVoteRequest $request): CastVoteResponse
    {
        $user = $this->userRepository->findById($request->getUserId());
        if ($user === null) {
            throw new \RuntimeException('error.user_not_found');
        }
        $poll = $this->pollRepository->findById($request->getPollId());
        if ($poll === null) {
            throw new \RuntimeException('error.poll_not_found');
        }
        $option = null;
        if (method_exists($this->pollRepository, 'findOptionsByPollId')) {
            $options = $this->pollRepository->findOptionsByPollId($poll->getId());
            foreach ($options as $opt) {
                if (method_exists($opt, 'getId') && $opt->getId() === $request->getOptionId()) {
                    $option = $opt;
                    break;
                }
            }
        }
        if ($option === null) {
            throw new \RuntimeException('error.option_not_in_poll');
        }
       $existingVote = null;
        if (method_exists($this->voteRepository, 'findByUserAndPoll')) {
            $existingVote = $this->voteRepository->findByUserAndPoll(
                $request->getUserId(),
                $request->getPollId()
            );
        }
        $existingVotes = $existingVote === null ? [] : [$existingVote];
        $this->votePolicyService->assertCanVote(
            $poll,
            $user->getId(),
            $existingVotes,
            $request->getIpAddress(),
            $request->getUserAgent()
        );

        if ($existingVote !== null) {
            $existingVote->changeOption($option->getId());
            $savedVote = $this->voteRepository->save($existingVote);
        } else {
            $vote = new Vote(
                null,
                $poll->getId(),
                $option->getId(),
                $user->getId(),
                new \DateTimeImmutable(),      // 5: createdAt
                $request->getIpAddress(),      // 6: IP
                $request->getUserAgent(),      // 7: User-Agent
                null                           // 8: дополнительное поле (reason/что-то ещё)
            );

            $savedVote = $this->voteRepository->save($vote);
        }

        // 6. Логируем попытку голосования, если репозиторий умеет
        if (method_exists($this->voteRepository, 'logAttempt')) {
            $this->voteRepository->logAttempt(
                $poll->getId(),
                $request->getUserId(),
                $request->getIpAddress(),
                $request->getUserAgent(),
                $savedVote->getId(),
                null
            );
        }

        return new CastVoteResponse(true, 'vote.success');
    }
}

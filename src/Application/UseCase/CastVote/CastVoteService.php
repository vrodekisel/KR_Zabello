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
        $this->pollRepository = $pollRepository;
        $this->userRepository = $userRepository;
        $this->voteRepository = $voteRepository;
        $this->votePolicyService = $votePolicyService;
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

        $option = $poll->findOptionById($request->getOptionId());
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

        $this->votePolicyService->assertCanVote($user, $poll, $existingVote);

        if ($existingVote !== null) {
            $existingVote->changeOption($option->getId());
            $savedVote = $this->voteRepository->save($existingVote);
        } else {
            $vote = new Vote(
                null,
                $poll->getId(),
                $option->getId(),
                $user->getId(),
                $request->getIpAddress(),
                $request->getUserAgent(),
                new \DateTimeImmutable()
            );

            $savedVote = $this->voteRepository->save($vote);
        }

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

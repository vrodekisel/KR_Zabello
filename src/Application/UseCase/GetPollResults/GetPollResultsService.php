<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetPollResults;

use App\Application\DTO\PollDTO;
use App\Domain\Repository\PollRepository;
use App\Domain\Repository\VoteRepository;

final class GetPollResultsService
{
    private PollRepository $pollRepository;
    private VoteRepository $voteRepository;

    public function __construct(
        PollRepository $pollRepository,
        VoteRepository $voteRepository
    ) {
        $this->pollRepository = $pollRepository;
        $this->voteRepository = $voteRepository;
    }

    public function handle(GetPollResultsRequest $request): GetPollResultsResponse
    {
        $poll = $this->pollRepository->findById($request->getPollId());

        if ($poll === null) {
            throw new \RuntimeException('error.poll_not_found');
        }

        if (method_exists($this->voteRepository, 'getResultsByPollId')) {
            $results = $this->voteRepository->getResultsByPollId($poll->getId());
        } else {
            $results = [];
        }

        $pollDTO = PollDTO::fromEntity($poll);

        return new GetPollResultsResponse($pollDTO, $results);
    }
}

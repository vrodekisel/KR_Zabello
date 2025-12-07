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
            // Ключ ошибки оставляем тем же, чтобы совпадало с остальным кодом
            throw new \RuntimeException('error.poll_not_found');
        }

        // Poll::getId() по идее не должен быть null для существующего опроса,
        // но на всякий случай подстрахуемся.
        $pollId = $poll->getId();

        if ($pollId === null) {
            $results = [];
        } else {
            // НОРМАЛЬНО используем контракт VoteRepository:
            // array<int,int> [optionId => votesCount]
            $results = $this->voteRepository->countByPollGroupedByOption($pollId);
        }

        $pollDTO = PollDTO::fromEntity($poll);

        return new GetPollResultsResponse($pollDTO, $results);
    }
}

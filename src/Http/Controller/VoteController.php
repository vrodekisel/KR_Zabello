<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\UseCase\CastVote\CastVoteService;
use App\Application\UseCase\CastVote\CastVoteRequest;
use App\Application\UseCase\GetPollResults\GetPollResultsService;
use App\Application\UseCase\GetPollResults\GetPollResultsRequest;

final class VoteController
{
    private CastVoteService $castVoteService;
    private GetPollResultsService $getPollResultsService;
    private AuthController $authController;

    public function __construct(
        CastVoteService $castVoteService,
        GetPollResultsService $getPollResultsService,
        AuthController $authController
    ) {
        $this->castVoteService       = $castVoteService;
        $this->getPollResultsService = $getPollResultsService;
        $this->authController        = $authController;
    }

    public function cast(): void
{
    $userId = $this->authController->getUserIdFromToken();
    if ($userId === null) {
        $this->jsonError('auth.error.token_required', 401);
        return;
    }

    // 1) Пытаемся прочитать JSON
    $input = $this->getJsonInput();

    // 2) Если JSON-полей нет, пробуем обычную форму (web-страница)
    if (empty($input)) {
        $input = $_POST;
    }

    $pollId   = isset($input['poll_id']) ? (int) $input['poll_id'] : 0;
    $optionId = isset($input['option_id']) ? (int) $input['option_id'] : 0;

    if ($pollId <= 0 || $optionId <= 0) {
        $this->jsonError('vote.error.invalid_payload', 400);
        return;
    }

    $ip        = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $request = new CastVoteRequest(
        $pollId,
        $optionId,
        $userId,
        $ip,
        $userAgent
    );

    try {
        $this->castVoteService->handle($request);
    } catch (\DomainException $e) {
        $this->jsonError($e->getMessage(), 400);
        return;
    }

    $this->jsonResponse([
        'message' => 'vote.cast.success',
    ], 201);
}


    public function results(): void
    {
        $pollId = isset($_GET['poll_id']) ? (int) $_GET['poll_id'] : 0;
        if ($pollId <= 0) {
            $this->jsonError('poll.error.invalid_id', 400);
            return;
        }

        $request  = new GetPollResultsRequest($pollId);
        $response = $this->getPollResultsService->handle($request);

        $pollDTO     = $response->getPoll();
        $results     = $response->getResults();      // optionId => count
        $percentages = $response->getPercentages();  // optionId => percent
        $total       = $response->getTotalVotes();

        $optionsData = [];
        foreach ($results as $optionId => $count) {
            $optionsData[] = [
                'option_id' => $optionId,
                'count'     => $count,
                'percent'   => $percentages[$optionId] ?? 0.0,
            ];
        }

        $this->jsonResponse([
            'message' => 'poll.results.success',
            'data'    => [
                'poll'    => [
                    'id'              => $pollDTO->getId(),
                    'title_key'       => $pollDTO->getTitleKey(),
                    'description_key' => $pollDTO->getDescriptionKey(),
                    'context_type'    => $pollDTO->getContextType(),
                    'status'          => $pollDTO->getStatus(),
                    'expires_at'      => $pollDTO->getExpiresAt()?->format(\DateTimeInterface::ATOM),
                ],
                'results' => $optionsData,
                'total'   => $total,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function jsonError(string $messageKey, int $statusCode): void
    {
        $this->jsonResponse(
            ['error' => $messageKey],
            $statusCode
        );
    }
}

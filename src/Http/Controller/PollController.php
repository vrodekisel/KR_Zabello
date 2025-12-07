<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Application\UseCase\CreatePoll\CreatePollRequest;
use App\Domain\Repository\PollRepository;
use App\Domain\Entity\Poll;

final class PollController
{
    private CreatePollService $createPollService;
    private PollRepository $pollRepository;
    private AuthController $authController;

    public function __construct(
        CreatePollService $createPollService,
        PollRepository $pollRepository,
        AuthController $authController
    ) {
        $this->createPollService = $createPollService;
        $this->pollRepository    = $pollRepository;
        $this->authController    = $authController;
    }

    /**
     * Список активных опросов по контенту (тип + id).
     * Ожидает query-параметры: content_type=map|mod, content_id=int
     */
    public function index(): void
    {
        $contentType = isset($_GET['content_type']) ? (string) $_GET['content_type'] : '';
        $contentId   = isset($_GET['content_id']) ? (int) $_GET['content_id'] : 0;

        if ($contentType === '' || $contentId <= 0) {
            $this->jsonError('poll.error.invalid_filter', 400);
            return;
        }

        $now = new \DateTimeImmutable();

        $polls = $this->pollRepository->findAllActiveByContent(
            $contentType,
            $contentId,
            $now
        );

        $data = array_map(
            static function (Poll $poll) use ($now): array {
                return [
                    'id'              => $poll->getId(),
                    'content_type'    => $poll->getContentType(),
                    'content_id'      => $poll->getContentId(),
                    'title_key'       => $poll->getTitleKey(),
                    'description_key' => $poll->getDescriptionKey(),
                    'status'          => $poll->getStatus(),
                    'is_active'       => $poll->isActive($now),
                    'starts_at'       => $poll->getStartsAt()?->format(\DateTimeInterface::ATOM),
                    'ends_at'         => $poll->getEndsAt()?->format(\DateTimeInterface::ATOM),
                    'created_by'      => $poll->getCreatedByUserId(),
                    'created_at'      => $poll->getCreatedAt()->format(\DateTimeInterface::ATOM),
                ];
            },
            $polls
        );

        $this->jsonResponse([
            'message' => 'poll.list.success',
            'data'    => $data,
        ]);
    }

    /**
     * Создать опрос.
     *
     * Ожидает JSON:
     * {
     *   "type": "map" | "mod",
     *   "content_id": 123,
     *   "title_key": "poll.title.xxx",
     *   "description_key": "poll.desc.xxx",  // опционально
     *   "options": ["option.label.map.ancient_forest", "..."],
     *   "expires_at": "2025-12-31T23:59:59Z" // опционально
     * }
     */
    public function create(): void
    {
        $userId = $this->authController->getUserIdFromToken();
        if ($userId === null) {
            $this->jsonError('auth.error.token_required', 401);
            return;
        }

        $input = $this->getJsonInput();

        $type           = isset($input['type']) ? (string) $input['type'] : '';
        $contentId      = isset($input['content_id']) ? (int) $input['content_id'] : 0;
        $titleKey       = isset($input['title_key']) ? (string) $input['title_key'] : '';
        $descriptionKey = isset($input['description_key']) ? (string) $input['description_key'] : null;
        $optionKeys     = isset($input['options']) && \is_array($input['options']) ? $input['options'] : [];
        $expiresAt      = null;

        if (isset($input['expires_at']) && is_string($input['expires_at']) && $input['expires_at'] !== '') {
            try {
                $expiresAt = new \DateTimeImmutable($input['expires_at']);
            } catch (\Exception $e) {
                $this->jsonError('poll.error.invalid_expires_at', 400);
                return;
            }
        }

        if ($type === '' || $contentId <= 0 || $titleKey === '' || empty($optionKeys)) {
            $this->jsonError('poll.error.invalid_payload', 400);
            return;
        }

        $request = new CreatePollRequest(
            $userId,          // creatorUserId
            $titleKey,
            $descriptionKey,
            $type,            // contextType (map|mod)
            $optionKeys,      // optionLabelKeys
            $expiresAt        // expiresAt (DateTimeImmutable|null)
        );

        try {
            $response = $this->createPollService->handle($request);
        } catch (\RuntimeException $e) {
            // Например: error.user_not_found, error.user_banned и т.п.
            $this->jsonError($e->getMessage(), 400);
            return;
        }

        $pollDTO = $response->getPoll();

        $this->jsonResponse([
            'message' => 'poll.create.success',
            'data'    => [
                'id'              => $pollDTO->getId(),
                'title_key'       => $pollDTO->getTitleKey(),
                'description_key' => $pollDTO->getDescriptionKey(),
                'context_type'    => $pollDTO->getContextType(),
                'status'          => $pollDTO->getStatus(),
                'expires_at'      => $pollDTO->getExpiresAt()?->format(\DateTimeInterface::ATOM),
            ],
        ], 201);
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

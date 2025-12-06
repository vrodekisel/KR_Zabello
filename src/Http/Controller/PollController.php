<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Application\UseCase\CreatePoll\CreatePollRequest;
use App\Domain\Repository\PollRepository;
use App\Domain\Entity\Poll;

class PollController
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

    public function index(): void
    {
        // Для простоты считаем, что у репозитория есть метод findAllActive()
        $polls = $this->pollRepository->findAllActive();

        $data = array_map(
            static function (Poll $poll): array {
                return [
                    'id'          => $poll->getId(),
                    'type'        => $poll->getType(),              // например: "map" / "mod"
                    'content_id'  => $poll->getContentId(),         // id карты/мода
                    'title_key'   => $poll->getTitleKey(),
                    'description_key' => $poll->getDescriptionKey(),
                    'is_active'   => $poll->isActive(),
                    'created_at'  => $poll->getCreatedAt()->format(\DateTimeInterface::ATOM),
                ];
            },
            $polls
        );

        $this->jsonResponse([
            'message' => 'poll.list.success',
            'data'    => $data,
        ]);
    }

    public function create(): void
    {
        $userId = $this->authController->getUserIdFromToken();
        if ($userId === null) {
            $this->jsonError('auth.error.token_required', 401);
            return;
        }

        $input = $this->getJsonInput();

        // Здесь мы ожидаем, что фронт/клиент игры передаёт ключи, а не строки:
        // type: "map" | "mod"
        // content_id: int (id карты/мода)
        // title_key: "poll.map.next_round.title"
        // description_key: "poll.map.next_round.description"
        // options: массив ключей для вариантов
        $type           = isset($input['type']) ? (string)$input['type'] : '';
        $contentId      = isset($input['content_id']) ? (int)$input['content_id'] : 0;
        $titleKey       = isset($input['title_key']) ? (string)$input['title_key'] : '';
        $descriptionKey = isset($input['description_key']) ? (string)$input['description_key'] : '';
        $optionKeys     = isset($input['options']) && is_array($input['options']) ? $input['options'] : [];

        if ($type === '' || $contentId <= 0 || $titleKey === '' || empty($optionKeys)) {
            $this->jsonError('poll.error.invalid_payload', 400);
            return;
        }

        $request = new CreatePollRequest(
            $type,
            $contentId,
            $titleKey,
            $descriptionKey,
            $optionKeys,
            $userId
        );

        $response = $this->createPollService->execute($request);

        $this->jsonResponse([
            'message' => 'poll.create.success',
            'data'    => [
                'poll_id' => $response->getPollId(),
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

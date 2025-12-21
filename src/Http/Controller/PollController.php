<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Domain\Repository\PollRepository;
use DateTimeImmutable;

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
        header('Content-Type: application/json; charset=utf-8');

        $now = new DateTimeImmutable();

        $contentType = $_GET['content_type'] ?? null;
        $contentKey  = $_GET['content_key'] ?? null;

        $polls = [];

        if ($contentType !== null && $contentKey !== null) {
            $polls = $this->pollRepository->findAllActiveByContent(
                (string) $contentType,
                (string) $contentKey,
                $now
            );
        } else {
            $contexts = [
                ['MAP', 'next_map'],
                ['MOD', 'better_grass'],
                ['MOD', 'popular_mod'],
            ];

            foreach ($contexts as [$type, $key]) {
                $list = $this->pollRepository->findAllActiveByContent(
                    $type,
                    $key,
                    $now
                );

                foreach ($list as $poll) {
                    $polls[] = $poll;
                }
            }
        }

        $data = array_map(
            static function ($poll) use ($now): array {
                $startsAt   = method_exists($poll, 'getStartsAt') ? $poll->getStartsAt() : null;
                $endsAt     = method_exists($poll, 'getEndsAt') ? $poll->getEndsAt() : null;
                $createdAt  = method_exists($poll, 'getCreatedAt') ? $poll->getCreatedAt() : null;

                return [
                    'id'              => $poll->getId(),
                    'title_key'       => $poll->getTitleKey(),
                    'description_key' => $poll->getDescriptionKey(),
                    'status'          => $poll->getStatus(),
                    'is_active'       => $poll->isActive($now),

                    'starts_at'       => $startsAt instanceof \DateTimeInterface
                        ? $startsAt->format(DATE_ATOM)
                        : null,
                    'ends_at'         => $endsAt instanceof \DateTimeInterface
                        ? $endsAt->format(DATE_ATOM)
                        : null,
                    'created_by'      => $poll->getCreatedByUserId(),
                    'created_at'      => $createdAt instanceof \DateTimeInterface
                        ? $createdAt->format(DATE_ATOM)
                        : null,
                ];
            },
            $polls
        );

        echo json_encode(
            ['data' => $data],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->authController->getAuthenticatedUser();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(
                ['error' => 'auth.error.unauthorized'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            return;
        }

        $rawBody = file_get_contents('php://input') ?: '';
        $data    = json_decode($rawBody, true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(
                ['error' => 'app.error.invalid_json'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            return;
        }

        $poll = $this->createPollService->createPoll($user, $data);

        echo json_encode(
            [
                'data' => method_exists($poll, 'toArray')
                    ? $poll->toArray()
                    : [
                        'id'              => $poll->getId(),
                        'title_key'       => $poll->getTitleKey(),
                        'description_key' => $poll->getDescriptionKey(),
                        'status'          => $poll->getStatus(),
                        'created_at'      => $poll->getCreatedAt()
                            ? $poll->getCreatedAt()->format(DATE_ATOM)
                            : null,
                        'created_by'      => $poll->getCreatedByUserId(),
                    ],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Domain\Entity\Poll;
use App\Domain\Entity\User;
use App\Domain\Repository\PollRepository;
use App\Domain\Repository\UserRepository;
use App\Localization\Translator;
use App\View\View;


final class WebAdminPollController
{
    private UserRepository $userRepository;
    private CreatePollService $createPollService;
    private PollRepository $pollRepository;
    private Translator $translator;
    private View $view;

    public function __construct(
        UserRepository $userRepository,
        CreatePollService $createPollService,
        PollRepository $pollRepository,
        Translator $translator,
        View $view
    ) {
        $this->userRepository    = $userRepository;
        $this->createPollService = $createPollService;
        $this->pollRepository    = $pollRepository;
        $this->translator        = $translator;
        $this->view              = $view;
    }

    private function t(string $key): string
    {
        return $this->translator->trans($key);
    }

    private function requireAdmin(string $redirectAfterLogin = '/web/admin/polls'): User
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;

        if (!is_int($userId) && is_numeric((string)$userId)) {
            $userId = (int) $userId;
        }

        if (!is_int($userId) || $userId <= 0) {
            $location = '/web/login?redirect=' . urlencode($redirectAfterLogin);
            header('Location: ' . $location);
            exit;
        }

        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            $_SESSION = [];
            $location = '/web/login?redirect=' . urlencode($redirectAfterLogin);
            header('Location: ' . $location);
            exit;
        }

        if ($user->getId() === 1 && !$user->isBanned()) {
            return $user;
        }

        if (method_exists($user, 'getRole') && defined(User::class . '::ROLE_ADMIN')) {
            if ($user->getRole() !== User::ROLE_ADMIN || $user->isBanned()) {
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo $this->t('ui.web.admin.error.forbidden');
                exit;
            }

            return $user;
        }

        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo $this->t('ui.web.admin.error.forbidden');
        exit;
    }

    public function listPolls(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->requireAdmin('/web/admin/polls');
        $now = new \DateTimeImmutable();
        $contexts = [
            ['MAP', 'next_map'],
            ['MOD', 'better_grass'],
            ['MOD', 'popular_mod'],
        ];
        /** @var Poll[] $polls */
        $polls = [];
        foreach ($contexts as [$type, $key]) {
            $list = $this->pollRepository->findAllActiveByContent($type, $key, $now);
            foreach ($list as $poll) {
                $polls[] = $poll;
            }
        }
        $rows = array_map(
            static function (Poll $poll) use ($now): array {
                $createdAt = $poll->getCreatedAt();
                $startsAt  = $poll->getStartsAt();
                $endsAt    = $poll->getEndsAt();

                return [
                    'id'           => $poll->getId(),
                    'title_key'    => $poll->getTitleKey(),
                    'context_type' => $poll->getContextType(),
                    'context_key'  => $poll->getContextKey(),
                    'status'       => $poll->getStatus(),
                    'is_active'    => $poll->isActive($now),
                    'created_at'   => $createdAt?->format(DATE_ATOM),
                    'starts_at'    => $startsAt?->format(DATE_ATOM),
                    'ends_at'      => $endsAt?->format(DATE_ATOM),
                ];
            },
            $polls
        );
        $this->view->render('admin_polls_list', [
            'pageTitle'       => $this->t('ui.web.admin.polls.list.title'),
            'heading'         => $this->t('ui.web.admin.polls.list.heading'),
            'createLinkText'  => $this->t('ui.web.admin.polls.create.link'),
            'polls'           => $rows,
        ]);
    }

    public function showCreateForm(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        $this->requireAdmin('/web/admin/polls/create');

        $errorKey = isset($_GET['error']) ? (string) $_GET['error'] : null;
        $errorText = $errorKey !== null && $errorKey !== ''
            ? $this->t($errorKey)
            : null;

        $this->view->render('admin_poll_create', [
            'pageTitle'        => $this->t('ui.web.admin.polls.create.title'),
            'heading'          => $this->t('ui.web.admin.polls.create.heading'),
            'titleLabel'       => $this->t('ui.web.admin.polls.create.field.title_key'),
            'descriptionLabel' => $this->t('ui.web.admin.polls.create.field.description_key'),
            'contextTypeLabel' => $this->t('ui.web.admin.polls.create.field.context_type'),
            'contextKeyLabel'  => $this->t('ui.web.admin.polls.create.field.context_key'),
            'optionsLabel'     => $this->t('ui.web.admin.polls.create.field.options'),
            'optionLabelBase'  => $this->t('ui.web.admin.polls.create.field.option_label'),
            'buttonText'       => $this->t('ui.web.admin.polls.create.button.save'),
            'helperText'       => $this->t('ui.web.admin.polls.create.helper'),
            'errorText'        => $errorText,
        ]);
    }

    public function handleCreate(): void
    {
        $user = $this->requireAdmin('/web/admin/polls/create');

        $titleKey       = trim((string)($_POST['title_key'] ?? ''));
        $descriptionKey = trim((string)($_POST['description_key'] ?? ''));
        $contextType    = trim((string)($_POST['context_type'] ?? ''));
        $contextKey     = trim((string)($_POST['context_key'] ?? ''));

        $options = [];
        for ($i = 1; $i <= 4; $i++) {
            $value = trim((string)($_POST['option_' . $i] ?? ''));
            if ($value !== '') {
                $options[] = ['label_key' => $value];
            }
        }

        if ($titleKey === '' || $contextType === '' || $contextKey === '' || count($options) < 2) {
            $location = '/web/admin/polls/create?error='
                . urlencode('ui.web.admin.polls.create.error_validation');
            header('Location: ' . $location);
            exit;
        }

        $payload = [
            'title_key'       => $titleKey,
            'description_key' => $descriptionKey !== '' ? $descriptionKey : null,
            'context_type'    => $contextType,
            'context_key'     => $contextKey,
            'options'         => $options,
        ];

        try {
            $poll = $this->createPollService->createPoll($user, $payload);
        } catch (\Throwable $e) {
            error_log('ADMIN_CREATE_POLL_ERROR: ' . $e->getMessage());

            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');

            echo "Error while creating poll:\n";
            echo $e->getMessage();
            exit;
        }

        $pollId = method_exists($poll, 'getId') ? $poll->getId() : null;
        if ($pollId === null) {
            header('Location: /web/polls');
            exit;
        }

        header('Location: /web/poll?poll_id=' . (int) $pollId);
        exit;
    }

}

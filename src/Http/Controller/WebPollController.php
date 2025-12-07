<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\UseCase\CastVote\CastVoteRequest;
use App\Application\UseCase\CastVote\CastVoteService;
use App\Application\UseCase\GetPollResults\GetPollResultsRequest;
use App\Application\UseCase\GetPollResults\GetPollResultsService;
use App\Domain\Repository\PollRepository;
use App\Localization\Translator;
use App\View\View;
use DateTimeImmutable;

/**
 * HTML-клиент: список опросов, страница опроса и результаты.
 */
final class WebPollController
{
    private PollRepository $pollRepository;
    private CastVoteService $castVoteService;
    private GetPollResultsService $getPollResultsService;
    private Translator $translator;
    private View $view;

    public function __construct(
        PollRepository $pollRepository,
        CastVoteService $castVoteService,
        GetPollResultsService $getPollResultsService,
        Translator $translator,
        View $view
    ) {
        $this->pollRepository        = $pollRepository;
        $this->castVoteService       = $castVoteService;
        $this->getPollResultsService = $getPollResultsService;
        $this->translator            = $translator;
        $this->view                  = $view;
    }

    private function t(string $key): string
    {
        return $this->translator->trans($key);
    }

    // ===========================
    // Список опросов (главная)
    // ===========================

    /**
     * GET / или GET /web/polls
     */
    public function list(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        $now = new DateTimeImmutable();

        // Берём несколько "контекстов" как в JSON-контроллере
        $contexts = [
            ['MAP', 'next_map'],
            ['MOD', 'better_grass'],
            ['MOD', 'popular_mod'],
        ];

        $pollsById = [];

        foreach ($contexts as [$type, $key]) {
            $list = $this->pollRepository->findAllActiveByContent($type, $key, $now);
            foreach ($list as $poll) {
                $id = $poll->getId();
                if ($id === null) {
                    continue;
                }
                $pollsById[$id] = $poll;
            }
        }

        $activeLabel = $this->t('ui.web.polls.status.active');
        $closedLabel = $this->t('ui.web.polls.status.closed');

        $cards = [];
        foreach ($pollsById as $poll) {
            $id         = $poll->getId();
            $titleKey   = $poll->getTitleKey();
            $createdAt  = $poll->getCreatedAt();
            $isActive   = $poll->isActive($now);

            $cards[] = [
                'id'          => $id,
                'title'       => $this->t($titleKey),
                'statusLabel' => $isActive ? $activeLabel : $closedLabel,
                'created'     => $createdAt instanceof \DateTimeInterface
                    ? $createdAt->format('Y-m-d H:i')
                    : null,
            ];
        }

        $this->view->render('polls_list', [
            'pageTitle'      => $this->t('ui.web.polls.title'),
            'cards'          => $cards,
            'emptyText'      => $this->t('ui.web.polls.empty'),
            'openButtonText' => $this->t('ui.web.polls.button.open'),
            'baseStyles'     => $this->buildBaseStyles(),
            'topBarHtml'     => $this->buildTopBar('polls'),
        ]);
    }

    // ===========================
    // Страница опроса + результаты
    // ===========================

    /**
     * GET /web/poll?poll_id={id}
     */
    public function show(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        $pollId = isset($_GET['poll_id']) ? (int) $_GET['poll_id'] : 0;
        if ($pollId <= 0) {
            $title = $this->t('ui.poll.error.invalid_id');
            http_response_code(400);
            echo '<!doctype html><html><head><meta charset="utf-8"><title>'
                . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</title></head><body>';
            echo '<h1>' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h1>';
            echo '</body></html>';
            return;
        }

        $poll = $this->pollRepository->findById($pollId);
        if ($poll === null) {
            $title = $this->t('ui.poll.error.not_found');
            http_response_code(404);
            echo '<!doctype html><html><head><meta charset="utf-8"><title>'
                . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</title></head><body>';
            echo '<h1>' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h1>';
            echo '</body></html>';
            return;
        }

        $status   = isset($_GET['status']) ? (string) $_GET['status'] : null;
        $errorKey = isset($_GET['error_key']) ? (string) $_GET['error_key'] : null;

        $cookieName = 'poll_voted_' . $pollId;
        $hasVoted   = ($_COOKIE[$cookieName] ?? '') === '1' || $status === 'success';

        $titleKey       = method_exists($poll, 'getTitleKey') ? (string) $poll->getTitleKey() : 'ui.poll.title.unknown';
        $descriptionKey = method_exists($poll, 'getDescriptionKey') ? (string) $poll->getDescriptionKey() : null;

        $pollTitle       = $this->t($titleKey);
        $pollDescription = $descriptionKey !== null ? $this->t($descriptionKey) : null;

        $options = method_exists($poll, 'getOptions') ? $poll->getOptions() : [];

        $optionsView = [];
        $totalVotes  = 0;

        if ($hasVoted) {
            $resultsRequest  = new GetPollResultsRequest($pollId);
            $resultsResponse = $this->getPollResultsService->handle($resultsRequest);

            $counts      = $resultsResponse->getResults();     // [optionId => count]
            $percentages = $resultsResponse->getPercentages(); // [optionId => percent]
            $totalVotes  = $resultsResponse->getTotalVotes();

            foreach ($options as $option) {
                $optionId = method_exists($option, 'getId') ? (int) $option->getId() : 0;
                if ($optionId <= 0) {
                    continue;
                }

                $labelKey = method_exists($option, 'getLabelKey')
                    ? (string) $option->getLabelKey()
                    : 'ui.option.label.unknown';

                $count   = $counts[$optionId] ?? 0;
                $percent = $percentages[$optionId] ?? 0.0;

                $optionsView[] = [
                    'id'     => $optionId,
                    'label'  => $this->t($labelKey),
                    'count'  => $count,
                    'percent'=> $percent,
                ];
            }
        } else {
            foreach ($options as $option) {
                $optionId = method_exists($option, 'getId') ? (int) $option->getId() : 0;
                if ($optionId <= 0) {
                    continue;
                }

                $labelKey = method_exists($option, 'getLabelKey')
                    ? (string) $option->getLabelKey()
                    : 'ui.option.label.unknown';

                $optionsView[] = [
                    'id'     => $optionId,
                    'label'  => $this->t($labelKey),
                    'count'  => 0,
                    'percent'=> 0.0,
                ];
            }
        }

        $successMessage = $status === 'success'
            ? $this->t('ui.poll.message.vote_success')
            : null;

        $errorMessage = ($status === 'error' && $errorKey !== null && $errorKey !== '')
            ? $this->t($errorKey)
            : null;

        $this->view->render('poll_show', [
            'pageTitle'        => $this->t('ui.page.poll.title'),
            'baseStyles'       => $this->buildBaseStyles(),
            'topBarHtml'       => $this->buildTopBar('polls'),
            'pollId'           => $pollId,
            'status'           => $status,
            'successMessage'   => $successMessage,
            'errorMessage'     => $errorMessage,
            'hasVoted'         => $hasVoted,
            'pollTitle'        => $pollTitle,
            'pollDescription'  => $pollDescription,
            'optionsView'      => $optionsView,
            'totalVotes'       => $totalVotes,
            'voteButtonText'   => $this->t('ui.poll.button.vote'),
            'alreadyVotedText' => $this->t('ui.poll.button.already_voted'),
            'resultsTitle'     => $this->t('ui.poll.results.title'),
            'votesLabel'       => $this->t('ui.poll.results.votes'),
            'totalVotesLabel'  => $this->t('ui.poll.results.total_votes'),
        ]);
    }

    // ===========================
    // Голосование из HTML-формы
    // ===========================

    /**
     * POST /web/poll/vote
     */
    public function vote(): void
    {
        $pollId   = isset($_POST['poll_id']) ? (int) $_POST['poll_id'] : 0;
        $optionId = isset($_POST['option_id']) ? (int) $_POST['option_id'] : 0;

        if ($pollId <= 0 || $optionId <= 0) {
            $this->redirectToPoll($pollId, 'error', 'vote.error.invalid_payload');
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!is_int($userId) || $userId <= 0) {
            $redirect = '/web/poll?poll_id=' . $pollId;
            $location = '/web/login?redirect=' . urlencode($redirect);
            header('Location: ' . $location);
            exit;
        }

        $ip        = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

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
            $this->redirectToPoll($pollId, 'error', $e->getMessage());
            return;
        }

        $cookieName = 'poll_voted_' . $pollId;
        setcookie($cookieName, '1', [
            'expires'  => time() + 365 * 24 * 60 * 60,
            'path'     => '/',
            'secure'   => false,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);

        $this->redirectToPoll($pollId, 'success', null);
    }

    private function redirectToPoll(int $pollId, string $status, ?string $errorKey): void
    {
        if ($pollId <= 0) {
            $pollId = 0;
        }

        $location = '/web/poll?poll_id=' . $pollId . '&status=' . urlencode($status);
        if ($errorKey !== null && $errorKey !== '') {
            $location .= '&error_key=' . urlencode($errorKey);
        }

        header('Location: ' . $location);
        exit;
    }

    // ===========================
    // Вспомогательные методы для стилей и шапки
    // ===========================

    private function buildBaseStyles(): string
    {
        return '<style>
            body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 24px; background: #111827; color: #E5E7EB; }
            .container { max-width: 720px; margin: 0 auto; }
            .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
            .topbar-title { font-size: 18px; font-weight: 600; }
            .topbar-right { display: flex; gap: 12px; align-items: center; font-size: 14px; }
            .nav-link { color: #9CA3AF; text-decoration: none; margin-right: 8px; }
            .nav-link-active { color: #F9FAFB; font-weight: 500; }
            .lang-link { color: #9CA3AF; text-decoration: none; margin-left: 4px; }
            .lang-link-active { color: #F9FAFB; font-weight: 600; }
            .page-title { font-size: 22px; margin-bottom: 16px; }
            .card { background: #1F2937; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
            .card-title { font-size: 18px; margin-bottom: 8px; }
            .card-subtitle { font-size: 14px; margin-bottom: 16px; color: #9CA3AF; }
            .option-list { list-style: none; padding: 0; margin: 0 0 16px 0; }
            .option-item { margin-bottom: 8px; }
            .btn { display: inline-block; padding: 8px 16px; border-radius: 999px; border: none; cursor: pointer; font-size: 14px; text-decoration: none; }
            .btn-primary { background: #2563EB; color: #F9FAFB; }
            .btn-primary:hover { background: #1D4ED8; }
            .btn-primary[disabled] { opacity: 0.5; cursor: default; }
            .btn-secondary { background: transparent; color: #9CA3AF; border: 1px solid #4B5563; }
            .btn-secondary:hover { background: #111827; }
            .btn-small { font-size: 13px; padding: 4px 10px; }
            .back-link { margin-bottom: 12px; }
            .alert { padding: 8px 12px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
            .alert-success { background: #064E3B; color: #A7F3D0; }
            .alert-error { background: #7F1D1D; color: #FECACA; }
            .results-header { font-size: 16px; margin-bottom: 8px; }
            .result-row { margin-bottom: 8px; }
            .result-label { font-size: 14px; margin-bottom: 4px; }
            .result-bar { height: 10px; border-radius: 999px; background: #374151; overflow: hidden; }
            .result-bar-inner { height: 10px; border-radius: 999px; background: #10B981; }
            .result-meta { font-size: 12px; color: #9CA3AF; margin-top: 2px; }
        </style>';
    }

    private function buildTopBar(string $activeMenu): string
    {
        $locale   = $this->translator->getLocale();
        $userId   = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? null;

        // Текущий путь (без учёта query)
        $currentUri  = $_SERVER['REQUEST_URI'] ?? '/';
        $currentPath = parse_url($currentUri, PHP_URL_PATH) ?: '/';

        $html  = '<div class="topbar">';
        $html .= '<div class="topbar-left">';
        $html .= '<span class="topbar-title">'
            . htmlspecialchars($this->t('ui.app.title'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '</span> ';

        $html .= '<a href="/web/polls" class="nav-link'
            . ($activeMenu === 'polls' ? ' nav-link-active' : '')
            . '">Polls</a>';

        $html .= '</div>';

        $html .= '<div class="topbar-right">';

        // ----- Переключатель языка -----
        $html .= '<span>' . htmlspecialchars($this->t('ui.web.header.language'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ': ';

        foreach (['en', 'ru'] as $lang) {
            // Берём все текущие GET-параметры (poll_id, status и т.д.)
            $params = $_GET;
            $params['lang'] = $lang;

            $queryString = http_build_query($params);
            $href = $currentPath . ($queryString !== '' ? '?' . $queryString : '');

            $class = 'lang-link' . ($locale === $lang ? ' lang-link-active' : '');

            $html .= '<a href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
                . ' class="' . $class . '">'
                . strtoupper($lang)
                . '</a>';
        }

        $html .= '</span>';

        // ----- Блок пользователя -----
        if (is_int($userId) && $userId > 0 && is_string($username)) {
            $html .= '<span>'
                . htmlspecialchars($this->t('ui.web.header.logged_in_as'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . ' ' . htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</span>';
            $html .= '<form method="post" action="/web/logout" style="margin:0;">';
            $html .= '<button type="submit" class="btn btn-primary">'
                . htmlspecialchars($this->t('ui.web.header.logout'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</button>';
            $html .= '</form>';
        } else {
            $html .= '<a href="/web/login" class="btn btn-primary">'
                . htmlspecialchars($this->t('ui.web.header.login'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</a>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

<?php

declare(strict_types=1);

use App\Http\Router;
use App\Http\Controller\AuthController;
use App\Http\Controller\PollController;
use App\Http\Controller\VoteController;
use App\Http\Controller\WebPollController;
use App\Http\Controller\WebAuthController;
use App\Http\Controller\WebAdminPollController;
use App\Http\Controller\WebAdminPollDetailsController;

use App\Infrastructure\Persistence\MySQLConnection;
use App\Infrastructure\Persistence\MySQLUserRepository;
use App\Infrastructure\Persistence\MySQLPollRepository;
use App\Infrastructure\Persistence\MySQLVoteRepository;
use App\Infrastructure\Logging\FileLogger;

use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Application\UseCase\CastVote\CastVoteService;
use App\Application\UseCase\GetPollResults\GetPollResultsService;
use App\Domain\Service\VotePolicyService;

use App\Localization\Translator;
use App\View\View;

require __DIR__ . '/../vendor/autoload.php';

// Таймзона
date_default_timezone_set('UTC');

// Глобальный обработчик непойманных исключений (для отладки)
set_exception_handler(function (Throwable $e): void {
    error_log(sprintf(
        "UNHANDLED EXCEPTION: %s in %s:%d\nStack trace:\n%s",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        [
            'error'   => 'app.error.unhandled_exception',
            'message' => $e->getMessage(),
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
});

// ------------------------------
// Сессии для веб-авторизации
// ------------------------------

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------
// Загрузка конфигов
// ------------------------------

/** @var array<string,mixed> $config */
$config = require __DIR__ . '/../config/config.php';

/** @var array<string,mixed> $dbConfig */
$dbConfig = require __DIR__ . '/../config/db.php';

/** @var array<string,mixed> $localizationConfig */
$localizationConfig = require __DIR__ . '/../config/localization.php';

// ------------------------------
// Определяем текущий язык
// ------------------------------

$availableLocales = $localizationConfig['available_locales'] ?? ['en'];
$defaultLocale    = $localizationConfig['default_locale'] ?? 'en';
$fallbackLocale   = $localizationConfig['fallback_locale'] ?? $defaultLocale;

// 1) ?lang=ru
$requestedLocale = null;
if (isset($_GET['lang'])) {
    $candidate = (string) $_GET['lang'];
    if (in_array($candidate, $availableLocales, true)) {
        $requestedLocale = $candidate;
    }
}

// 2) cookie lang
if ($requestedLocale === null && isset($_COOKIE['lang'])) {
    $candidate = (string) $_COOKIE['lang'];
    if (in_array($candidate, $availableLocales, true)) {
        $requestedLocale = $candidate;
    }
}

// 3) дефолт
$locale = $requestedLocale ?? $defaultLocale;

// Запоминаем язык в cookie
setcookie('lang', $locale, [
    'expires'  => time() + 365 * 24 * 60 * 60,
    'path'     => '/',
    'secure'   => false,
    'httponly' => false,
    'samesite' => 'Lax',
]);

$langPath   = __DIR__ . '/../lang';
$translator = new Translator($locale, $fallbackLocale, $langPath);

// ------------------------------
// Инфраструктура: БД и логгер
// ------------------------------

$connection = new MySQLConnection($dbConfig);

$userRepository = new MySQLUserRepository($connection);
$pollRepository = new MySQLPollRepository($connection);
$voteRepository = new MySQLVoteRepository($connection);

$logFilePath = __DIR__ . '/../storage/logs/app.log';
$logger      = new FileLogger($logFilePath);

// ------------------------------
// Прикладной слой: UseCase'ы
// ------------------------------

$createPollService = new CreatePollService(
    $pollRepository,
    $userRepository
);

$votePolicyService = new VotePolicyService(10);

$castVoteService = new CastVoteService(
    $pollRepository,
    $userRepository,
    $voteRepository,
    $votePolicyService
);

$getPollResultsService = new GetPollResultsService(
    $pollRepository,
    $voteRepository
);

// ------------------------------
// View-слой
// ------------------------------

$view = new View($translator, $availableLocales);

// ------------------------------
// HTTP-слой: JSON API контроллеры
// ------------------------------

$authController = new AuthController(
    $userRepository,
    $config
);

$pollController = new PollController(
    $createPollService,
    $pollRepository,
    $authController
);

$voteController = new VoteController(
    $castVoteService,
    $getPollResultsService,
    $authController
);

// ------------------------------
// HTTP-слой: HTML фронт
// ------------------------------

// Веб-логин на сессиях
$webAuthController = new WebAuthController(
    $userRepository,
    $translator,
    $availableLocales,
    $view
);

// Список опросов, страница опроса и голосование
$webPollController = new WebPollController(
    $pollRepository,
    $castVoteService,
    $getPollResultsService,
    $translator,
    $view
);

// Админский фронт: список и создание опросов
$webAdminPollController = new WebAdminPollController(
    $userRepository,
    $createPollService,
    $pollRepository,
    $translator,
    $view
);

// Админский фронт: детали опроса
$webAdminPollDetailsController = new WebAdminPollDetailsController(
    $translator,
    $view,
    $pollRepository
);

// ------------------------------
// Маршруты
// ------------------------------

$routes = [
    // -------- JSON API: аутентификация --------
    [
        'method'  => 'POST',
        'path'    => '/auth/register',
        'handler' => [$authController, 'register'],
    ],
    [
        'method'  => 'POST',
        'path'    => '/auth/login',
        'handler' => [$authController, 'login'],
    ],

    // -------- JSON API: опросы --------
    [
        'method'  => 'GET',
        'path'    => '/polls',
        'handler' => [$pollController, 'index'],
    ],
    [
        'method'  => 'POST',
        'path'    => '/polls',
        'handler' => [$pollController, 'create'],
    ],

    // -------- JSON API: голосование и результаты --------
    [
        'method'  => 'POST',
        'path'    => '/vote',
        'handler' => [$voteController, 'cast'],
    ],
    [
        'method'  => 'GET',
        'path'    => '/results',
        'handler' => [$voteController, 'results'],
    ],

    // -------- HTML фронт: список опросов --------
    [
        'method'  => 'GET',
        'path'    => '/',
        'handler' => [$webPollController, 'list'],
    ],
    [
        'method'  => 'GET',
        'path'    => '/web/polls',
        'handler' => [$webPollController, 'list'],
    ],

    // -------- HTML фронт: страница опроса --------
    [
        'method'  => 'GET',
        'path'    => '/web/poll',
        'handler' => [$webPollController, 'show'],
    ],
    [
        'method'  => 'POST',
        'path'    => '/web/poll/vote',
        'handler' => [$webPollController, 'vote'],
    ],

    // -------- HTML фронт: логин / логаут --------
    [
        'method'  => 'GET',
        'path'    => '/web/login',
        'handler' => [$webAuthController, 'showLogin'],
    ],
    [
        'method'  => 'POST',
        'path'    => '/web/login',
        'handler' => [$webAuthController, 'handleLogin'],
    ],
    [
        'method'  => 'POST',
        'path'    => '/web/logout',
        'handler' => [$webAuthController, 'logout'],
    ],

    // -------- HTML фронт: админка списка и создания опросов --------
    [
        'method'  => 'GET',
        'path'    => '/web/admin/polls',
        'handler' => [$webAdminPollController, 'listPolls'],
    ],
    [
        'method'  => 'GET',
        'path'    => '/web/admin/polls/create',
        'handler' => [$webAdminPollController, 'showCreateForm'],
    ],
    [
        'method'  => 'POST',
        'path'    => '/web/admin/polls/create',
        'handler' => [$webAdminPollController, 'handleCreate'],
    ],

    // -------- HTML фронт: детали опроса в админке --------
    [
        'method'  => 'GET',
        'path'    => '/web/admin/poll',
        'handler' => [$webAdminPollDetailsController, 'show'],
    ],
];

// ------------------------------
// Запуск роутера
// ------------------------------

$router = new Router($routes);
$router->dispatch();

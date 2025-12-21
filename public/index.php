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

date_default_timezone_set('UTC');

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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/** @var array<string,mixed> $config */
$config = require __DIR__ . '/../config/config.php';

/** @var array<string,mixed> $dbConfig */
$dbConfig = require __DIR__ . '/../config/db.php';

/** @var array<string,mixed> $localizationConfig */
$localizationConfig = require __DIR__ . '/../config/localization.php';


$availableLocales = $localizationConfig['available_locales'] ?? ['en'];
$defaultLocale    = $localizationConfig['default_locale'] ?? 'en';
$fallbackLocale   = $localizationConfig['fallback_locale'] ?? $defaultLocale;

$requestedLocale = null;
if (isset($_GET['lang'])) {
    $candidate = (string) $_GET['lang'];
    if (in_array($candidate, $availableLocales, true)) {
        $requestedLocale = $candidate;
    }
}

if ($requestedLocale === null && isset($_COOKIE['lang'])) {
    $candidate = (string) $_COOKIE['lang'];
    if (in_array($candidate, $availableLocales, true)) {
        $requestedLocale = $candidate;
    }
}

$locale = $requestedLocale ?? $defaultLocale;

setcookie('lang', $locale, [
    'expires'  => time() + 365 * 24 * 60 * 60,
    'path'     => '/',
    'secure'   => false,
    'httponly' => false,
    'samesite' => 'Lax',
]);

$langPath   = __DIR__ . '/../lang';
$translator = new Translator($locale, $fallbackLocale, $langPath);

$connection = new MySQLConnection($dbConfig);

$userRepository = new MySQLUserRepository($connection);
$pollRepository = new MySQLPollRepository($connection);
$voteRepository = new MySQLVoteRepository($connection);

$logFilePath = __DIR__ . '/../storage/logs/app.log';
$logger      = new FileLogger($logFilePath);

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

$view = new View($translator, $availableLocales);

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

$webAdminPollDetailsController = new WebAdminPollDetailsController(
    $translator,
    $view,
    $pollRepository
);


$routes = [
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

    [
        'method'  => 'GET',
        'path'    => '/web/admin/poll',
        'handler' => [$webAdminPollDetailsController, 'show'],
    ],
];
$router = new Router($routes);
$router->dispatch();

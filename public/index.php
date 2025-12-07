<?php

declare(strict_types=1);

use App\Http\Router;
use App\Http\Controller\AuthController;
use App\Http\Controller\PollController;
use App\Http\Controller\VoteController;
use App\Http\Controller\WebPollController;
use App\Infrastructure\Persistence\MySQLConnection;
use App\Infrastructure\Persistence\MySQLUserRepository;
use App\Infrastructure\Persistence\MySQLPollRepository;
use App\Infrastructure\Persistence\MySQLVoteRepository;
use App\Infrastructure\Logging\FileLogger;
use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Application\UseCase\CastVote\CastVoteService;
use App\Application\UseCase\GetPollResults\GetPollResultsService;
use App\Domain.Service\VotePolicyService;

require __DIR__ . '/../vendor/autoload.php';

// Можно выставить таймзону, чтобы даты были стабильнее
date_default_timezone_set('UTC');

// Глобальный обработчик непойманных исключений (временный, расширенный для отладки)
set_exception_handler(function (Throwable $e): void {
    // Логируем подробности в error_log Apache
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
            'message' => $e->getMessage(), // временно показываем текст ошибки
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
});

// ------------------------------
// Загрузка конфигов
// ------------------------------

/** @var array<string,mixed> $config */
$config = require __DIR__ . '/../config/config.php';

/** @var array<string,mixed> $dbConfig */
$dbConfig = require __DIR__ . '/../config/db.php';

// ------------------------------
// Инфраструктура: БД и логгер
// ------------------------------

$connection = new MySQLConnection($dbConfig);

$userRepository  = new MySQLUserRepository($connection);
$pollRepository  = new MySQLPollRepository($connection);
$voteRepository  = new MySQLVoteRepository($connection);

$logFilePath = __DIR__ . '/../storage/logs/app.log';
$logger      = new FileLogger($logFilePath);

// ------------------------------
// Прикладной слой: UseCase'ы
// ------------------------------

$createPollService = new CreatePollService(
    $pollRepository,
    $userRepository
);

$votePolicyService = new VotePolicyService(10); // можно оставить 10 или другое ограничение

$castVoteService = new CastVoteService(
    $pollRepository,
    $userRepository,
    $voteRepository,
    $votePolicyService
);

// Получение результатов и рейтингов
$getPollResultsService = new GetPollResultsService(
    $pollRepository,
    $voteRepository
);

// ------------------------------
// HTTP-слой: контроллеры (JSON API)
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
// HTTP-слой: простой HTML-клиент
// ------------------------------
//
// WebPollController — это демо-фронтенд на PHP, который
// рендерит HTML-страницу опроса и результатов на /web/poll.
//

$webPollController = new WebPollController(
    $pollRepository,
    $castVoteService,
    $getPollResultsService,
    $authController
);

// ------------------------------
// Маршруты
// ------------------------------
//
// JSON API (осталось как было)
//

$routes = [
    // Аутентификация
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

    // Работа с опросами за внутриигровой контент (карты, моды и т.д.)
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

    // Голосование и результаты (JSON API)
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

    // --------------------------
    // HTML-фронтенд (минимальная страница опроса)
    // --------------------------

    // Страница опроса + результаты на той же странице
    [
        'method'  => 'GET',
        'path'    => '/web/poll',
        'handler' => [$webPollController, 'show'],
    ],

    // Обработка формы голосования и редирект обратно на /web/poll
    [
        'method'  => 'POST',
        'path'    => '/web/poll/vote',
        'handler' => [$webPollController, 'vote'],
    ],
];

// ------------------------------
// Запуск роутера
// ------------------------------

$router = new Router($routes);
$router->dispatch();

<?php

declare(strict_types=1);

use App\Http\Router;
use App\Http\Controller\AuthController;
use App\Http\Controller\PollController;
use App\Http\Controller\VoteController;
use App\Infrastructure\Persistence\MySQLConnection;
use App\Infrastructure\Persistence\MySQLUserRepository;
use App\Infrastructure\Persistence\MySQLPollRepository;
use App\Infrastructure\Persistence\MySQLVoteRepository;
use App\Infrastructure\Logging\FileLogger;
use App\Application\UseCase\CreatePoll\CreatePollService;
use App\Application\UseCase\CastVote\CastVoteService;
use App\Application\UseCase\GetPollResults\GetPollResultsService;

require __DIR__ . '/../vendor/autoload.php';

// Можно выставить таймзону, чтобы даты были стабильнее
date_default_timezone_set('UTC');

// Глобальный обработчик непойманных исключений
set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        [
            'error' => 'app.error.unhandled_exception',
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

// Создание опроса (внутриигровое голосование за контент)
$createPollService = new CreatePollService(
    $pollRepository,
    $logger
);

// Голосование (учёт ограничений, логирование, защита от накрутки)
$castVoteService = new CastVoteService(
    $pollRepository,
    $voteRepository,
    $logger
);

// Получение результатов и рейтингов
$getPollResultsService = new GetPollResultsService(
    $pollRepository,
    $voteRepository
);

// ------------------------------
// HTTP-слой: контроллеры
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
// Маршруты
// ------------------------------
//
// Здесь для простоты маршруты задаются прямо в index.php.
// Позже можно вынести “описание” в config/routes.php,
// а здесь только превращать его в массив handler'ов.
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

    // Голосование и результаты
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
];

// ------------------------------
// Запуск роутера
// ------------------------------

$router = new Router($routes);
$router->dispatch();

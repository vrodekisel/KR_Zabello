<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\MySQLConnection;
use App\Infrastructure\Persistence\MySQLUserRepository;
use App\Infrastructure\Persistence\MySQLPollRepository;
use App\Infrastructure\Persistence\MySQLVoteRepository;
use App\Infrastructure\Logging\FileLogger;

require __DIR__ . '/../vendor/autoload.php';

// загружаем конфиг БД
$dbConfigPath = __DIR__ . '/../config/db.php';

$connection = MySQLConnection::fromConfigFile($dbConfigPath);

$userRepo = new MySQLUserRepository($connection);
$pollRepo = new MySQLPollRepository($connection);
$voteRepo = new MySQLVoteRepository($connection);
$logger   = new FileLogger();

// просто тестовые вызовы
try {
    $testUser = $userRepo->findById(1);
    $testPoll = $pollRepo->findById(1);
    $votesForPoll1 = $voteRepo->getByPollId(1);
    $countVotesUser1Poll1 = $voteRepo->countByUserAndPoll(1, 1);

    $logger->info('log_test_repositories_success', [
        'user_id'   => $testUser ? $testUser->toArray()['id'] ?? null : null,
        'poll_id'   => $testPoll ? $testPoll->toArray()['id'] ?? null : null,
        'votes_cnt' => $countVotesUser1Poll1,
    ]);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'status' => 'ok',
        'message' => 'log_test_repositories_success',
        'data' => [
            'user_1_exists'      => $testUser !== null,
            'poll_1_exists'      => $testPoll !== null,
            'votes_for_poll_1'   => count($votesForPoll1),
            'user_1_votes_poll_1'=> $countVotesUser1Poll1,
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    $logger->error('log_test_repositories_failed', [
        'exception_class' => get_class($e),
        'exception_code'  => $e->getCode(),
    ]);

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'status'  => 'error',
        'message' => 'log_test_repositories_failed',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

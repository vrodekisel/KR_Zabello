<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Vote;
use App\Domain\Repository\VoteRepository;
use DateTimeImmutable;
use PDO;

final class MySQLVoteRepository implements VoteRepository
{
    private PDO $pdo;

    public function __construct(MySQLConnection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    /**
     * Найти голос конкретного пользователя в конкретном опросе.
     */
    public function findByUserAndPoll(int $userId, int $pollId): ?Vote
    {
        $sql = 'SELECT * FROM votes WHERE user_id = :user_id AND poll_id = :poll_id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'poll_id' => $pollId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Vote::fromArray($row);
    }

    /**
     * Добавить новый голос.
     */
    public function add(Vote $vote): void
    {
        $data = $vote->toArray();
        // id генерируется в БД
        unset($data['id']);

        $columns = array_keys($data);
        $placeholders = array_map(
            static fn(string $col): string => ':' . $col,
            $columns
        );

        $sql = sprintf(
            'INSERT INTO votes (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        $newId = (int) $this->pdo->lastInsertId();

        // Проставляем id обратно в сущность Vote через reflection (если нужно)
        $reflection = new \ReflectionObject($vote);
        if ($reflection->hasProperty('id')) {
            $prop = $reflection->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($vote, $newId);
        }
    }

    /**
     * Подсчитать количество голосов по каждому варианту в опросе.
     *
     * @return array<int,int> key: optionId, value: votes count
     */
    public function countByPollGroupedByOption(int $pollId): array
    {
        $sql = <<<SQL
SELECT option_id, COUNT(*) AS cnt
FROM votes
WHERE poll_id = :poll_id
GROUP BY option_id
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['poll_id' => $pollId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $optionId = (int) $row['option_id'];
            $count    = (int) $row['cnt'];
            $result[$optionId] = $count;
        }

        return $result;
    }

    /**
     * Подсчитать количество голосов пользователя за недавний период.
     */
    public function countRecentVotesByUser(int $userId, DateTimeImmutable $since): int
    {
        $sql = <<<SQL
SELECT COUNT(*) AS cnt
FROM votes
WHERE user_id = :user_id
  AND created_at >= :since
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'since'   => $since->format('Y-m-d H:i:s'),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return 0;
        }

        return (int) $row['cnt'];
    }

    /**
     * Вспомогательный метод: сколько голосов поставил пользователь в конкретный опрос.
     * НЕ обязателен интерфейсом, но может использоваться в других местах.
     */
    public function countByUserAndPoll(int $userId, int $pollId): int
    {
        $sql = 'SELECT COUNT(*) AS cnt FROM votes WHERE user_id = :user_id AND poll_id = :poll_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'poll_id' => $pollId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return 0;
        }

        return (int) $row['cnt'];
    }

    /**
     * Обновление голоса (используется опционально, вне интерфейса).
     */
    public function save(Vote $vote): Vote
    {
        $data = $vote->toArray();
        $id = $data['id'] ?? null;

        if ($id === null) {
            $this->add($vote);
            return $vote;
        }

        $columns = array_keys($data);
        $columns = array_filter(
            $columns,
            static fn(string $col): bool => $col !== 'id'
        );

        $assignments = array_map(
            static fn(string $col): string => sprintf('%s = :%s', $col, $col),
            $columns
        );

        $sql = sprintf(
            'UPDATE votes SET %s WHERE id = :id',
            implode(', ', $assignments)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $vote;
    }

    /**
     * Получить все голоса по опросу.
     *
     * @return Vote[]
     */
    public function getByPollId(int $pollId): array
    {
        $sql = 'SELECT * FROM votes WHERE poll_id = :poll_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['poll_id' => $pollId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = Vote::fromArray($row);
        }

        return $result;
    }
}

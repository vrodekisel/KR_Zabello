<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Vote;
use App\Domain\Repository\VoteRepository;
use PDO;

class MySQLVoteRepository implements VoteRepository
{
    private PDO $pdo;

    public function __construct(MySQLConnection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function countByUserAndPoll(int $userId, int $pollId): int
    {
        $sql = 'SELECT COUNT(*) AS cnt FROM votes WHERE user_id = :user_id AND poll_id = :poll_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'poll_id' => $pollId,
        ]);

        $row = $stmt->fetch();

        if ($row === false) {
            return 0;
        }

        return (int)$row['cnt'];
    }

    public function save(Vote $vote): Vote
    {
        $data = $vote->toArray();
        $id = $data['id'] ?? null;

        if ($id === null) {
            unset($data['id']);

            $columns = array_keys($data);
            $placeholders = array_map(
                fn(string $col): string => ':' . $col,
                $columns
            );

            $sql = sprintf(
                'INSERT INTO votes (%s) VALUES (%s)',
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $newId = (int)$this->pdo->lastInsertId();
            $data['id'] = $newId;

            return Vote::fromArray($data);
        }

        $columns = array_keys($data);
        $columns = array_filter($columns, fn(string $col): bool => $col !== 'id');

        $assignments = array_map(
            fn(string $col): string => sprintf('%s = :%s', $col, $col),
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
     * @return Vote[]
     */
    public function getByPollId(int $pollId): array
    {
        $sql = 'SELECT * FROM votes WHERE poll_id = :poll_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['poll_id' => $pollId]);

        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = Vote::fromArray($row);
        }

        return $result;
    }
}

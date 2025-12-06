<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Poll;
use App\Domain\Repository\PollRepository;
use PDO;

class MySQLPollRepository implements PollRepository
{
    private PDO $pdo;

    public function __construct(MySQLConnection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function findById(int $id): ?Poll
    {
        $sql = 'SELECT * FROM polls WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return Poll::fromArray($row);
    }

    /**
     * @return Poll[]
     */
    public function findActiveByTarget(string $targetType, int $targetId): array
    {
        $sql = <<<SQL
SELECT *
FROM polls
WHERE target_type = :target_type
  AND target_id = :target_id
  AND status = :status_active
ORDER BY created_at DESC
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'target_type'   => $targetType,
            'target_id'     => $targetId,
            'status_active' => 'active',
        ]);

        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = Poll::fromArray($row);
        }

        return $result;
    }

    public function save(Poll $poll): Poll
    {
        $data = $poll->toArray();
        $id = $data['id'] ?? null;

        if ($id === null) {
            unset($data['id']);

            $columns = array_keys($data);
            $placeholders = array_map(
                fn(string $col): string => ':' . $col,
                $columns
            );

            $sql = sprintf(
                'INSERT INTO polls (%s) VALUES (%s)',
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $newId = (int)$this->pdo->lastInsertId();
            $data['id'] = $newId;

            return Poll::fromArray($data);
        }

        $columns = array_keys($data);
        $columns = array_filter($columns, fn(string $col): bool => $col !== 'id');

        $assignments = array_map(
            fn(string $col): string => sprintf('%s = :%s', $col, $col),
            $columns
        );

        $sql = sprintf(
            'UPDATE polls SET %s WHERE id = :id',
            implode(', ', $assignments)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $poll;
    }
}

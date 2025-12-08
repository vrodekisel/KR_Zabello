<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Poll;
use App\Domain\Entity\Option;
use App\Domain\Repository\PollRepository;
use DateTimeImmutable;
use PDO;

final class MySQLPollRepository implements PollRepository
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
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return Poll::fromArray($row);
    }
    public function findActiveById(int $id, DateTimeImmutable $now): ?Poll
    {
        $sql = <<<SQL
SELECT *
FROM polls
WHERE id = :id
  AND is_active = 1
  AND (expires_at IS NULL OR expires_at > :now)
LIMIT 1
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id'  => $id,
            'now' => $now->format('Y-m-d H:i:s'),
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return Poll::fromArray($row);
    }

    /**
     * Найти все активные опросы по типу контента и его ключу.
     *
     * @return Poll[]
     */
    public function findAllActiveByContent(
        string $contentType,
        string $contentKey,
        DateTimeImmutable $now
    ): array {
        $sql = <<<SQL
SELECT *
FROM polls
WHERE content_type = :content_type
  AND content_key  = :content_key
  AND is_active    = 1
  AND (expires_at IS NULL OR expires_at > :now)
ORDER BY created_at DESC
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'content_type' => $contentType,
            'content_key'  => $contentKey,
            'now'          => $now->format('Y-m-d H:i:s'),
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = Poll::fromArray($row);
        }

        return $result;
    }

    /**
     * Создать новый опрос (и, при необходимости, сохранить связанные варианты).
     *
     * @param Option[] $options
     */
    public function add(Poll $poll, array $options): void
    {
        $data = $poll->toArray();
        // id генерируется в БД
        unset($data['id']);

        $columns = array_keys($data);
        $placeholders = array_map(
            static fn (string $col): string => ':' . $col,
            $columns
        );

        $sql = sprintf(
            'INSERT INTO polls (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        $newId = (int) $this->pdo->lastInsertId();

        // Проставляем id обратно в сущность Poll через reflection
        $reflection = new \ReflectionObject($poll);
        if ($reflection->hasProperty('id')) {
            $prop = $reflection->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($poll, $newId);
        }

        // Если options пустой, выходим
        if ($options === []) {
            return;
        }

        // Сохраняем варианты в таблицу options
        $sqlOption = <<<SQL
INSERT INTO options (poll_id, label, value, position, created_at)
VALUES (:poll_id, :label, :value, :position, :created_at)
SQL;

        $stmtOption = $this->pdo->prepare($sqlOption);

        foreach ($options as $option) {
            if (!$option instanceof Option) {
                continue;
            }

            $optionData = $option->toArray();
            unset($optionData['id']);
            $optionData['poll_id']    = $newId;
            $optionData['created_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            $stmtOption->execute($optionData);
        }
    }

    /**
     * Обновить существующий опрос.
     */
    public function save(Poll $poll): void
    {
        $data = $poll->toArray();
        $id = $data['id'] ?? null;

        if ($id === null) {
            $this->add($poll, []);
            return;
        }

        $columns = array_keys($data);
        $columns = array_filter(
            $columns,
            static fn (string $col): bool => $col !== 'id'
        );

        $assignments = array_map(
            static fn (string $col): string => sprintf('%s = :%s', $col, $col),
            $columns
        );

        $sql = sprintf(
            'UPDATE polls SET %s WHERE id = :id',
            implode(', ', $assignments)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    /**
     * Получить варианты ответа для опроса.
     *
     * @return Option[]
     */
    public function findOptionsByPollId(int $pollId): array
    {
        $sql = <<<SQL
SELECT *
FROM options
WHERE poll_id = :poll_id
ORDER BY position ASC, id ASC
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['poll_id' => $pollId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = Option::fromArray($row);
        }

        return $result;
    }
}

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

    /**
     * Найти активный опрос по id с учётом статуса и (опционально) времени жизни.
     */
    public function findActiveById(int $id, DateTimeImmutable $now): ?Poll
    {
        // Предполагаем, что в таблице есть:
        // - status (например, 'active')
        // - expires_at (может быть NULL)
        $sql = <<<SQL
SELECT *
FROM polls
WHERE id = :id
  AND status = :status_active
  AND (expires_at IS NULL OR expires_at > :now)
LIMIT 1
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id'            => $id,
            'status_active' => 'active',
            'now'           => $now->format('Y-m-d H:i:s'),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Poll::fromArray($row);
    }

    /**
     * Найти все активные опросы по типу контента и его id.
     *
     * @return Poll[]
     */
    public function findAllActiveByContent(
        string $contentType,
        int $contentId,
        DateTimeImmutable $now
    ): array {
        // Здесь я опираюсь на уже существующую у тебя логику findActiveByTarget:
        // в БД, судя по старому коду, есть target_type / target_id / status.
        // Аргументы contentType/contentId просто маппим на эти поля.
        $sql = <<<SQL
SELECT *
FROM polls
WHERE target_type = :content_type
  AND target_id   = :content_id
  AND status      = :status_active
  AND (expires_at IS NULL OR expires_at > :now)
ORDER BY created_at DESC
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'content_type'  => $contentType,
            'content_id'    => $contentId,
            'status_active' => 'active',
            'now'           => $now->format('Y-m-d H:i:s'),
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = Poll::fromArray($row);
        }

        return $result;
    }

    /**
     * Создать новый опрос (и, опционально, сохранить связанные варианты).
     *
     * @param Option[] $options
     */
    public function add(Poll $poll, array $options): void
    {
        $data = $poll->toArray();
        // На вставку id не отправляем
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

        $newId = (int)$this->pdo->lastInsertId();

        // Если нужно, прописываем id обратно в сущность Poll:
        $reflection = new \ReflectionObject($poll);
        if ($reflection->hasProperty('id')) {
            $prop = $reflection->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($poll, $newId);
        }

        // Варианты (options) сейчас можно либо игнорировать,
        // либо сохранить в отдельную таблицу (например, poll_options).
        // Чтобы не ломать проект, делаем максимально мягко:
        if ($options === []) {
            return;
        }

        // Если у тебя есть таблица poll_options, этот блок заработает практически сразу,
        // иначе просто можно будет подправить названия колонок.
        $sqlOption = <<<SQL
INSERT INTO poll_options (poll_id, label_key, sort_order, created_at)
VALUES (:poll_id, :label_key, :sort_order, :created_at)
SQL;

        $stmtOption = $this->pdo->prepare($sqlOption);
        $sort = 0;

        foreach ($options as $option) {
            // Ожидаем Option-сущности, но чтобы не падать, проверяем:
            if ($option instanceof Option) {
                $labelKey = $option->getLabelKey();
            } else {
                // На крайний случай — строка-лейбл
                $labelKey = (string)$option;
            }

            $stmtOption->execute([
                'poll_id'    => $newId,
                'label_key'  => $labelKey,
                'sort_order' => $sort++,
                'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
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
            // Если вдруг save вызвали без id — считаем это вставкой без options.
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
        // Опираемся на предположение, что есть таблица poll_options.
        // Если она у тебя называется иначе — только SQL нужно будет подправить.
        $sql = <<<SQL
SELECT *
FROM poll_options
WHERE poll_id = :poll_id
ORDER BY sort_order ASC, id ASC
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['poll_id' => $pollId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            // Если у Option есть fromArray – супер, используем.
            // Если нет – потом заменим на конструктор/сетеры.
            $result[] = Option::fromArray($row);
        }

        return $result;
    }
}

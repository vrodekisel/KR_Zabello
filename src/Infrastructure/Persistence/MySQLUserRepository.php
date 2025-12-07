<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use PDO;

class MySQLUserRepository implements UserRepository
{
    private PDO $pdo;

    public function __construct(MySQLConnection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function findById(int $id): ?User
    {
        $sql = 'SELECT * FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return User::fromArray($row);
    }

    public function findByUsername(string $username): ?User
    {
        $sql = 'SELECT * FROM users WHERE username = :username LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username]);

        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return User::fromArray($row);
    }

    /**
     * Добавление нового пользователя (INSERT).
     */
    public function add(User $user): void
    {
        $data = $user->toArray();

        // Эти поля не отправляем в INSERT:
        // id — автоинкремент в БД
        // role — колонки в таблице нет, это чисто PHP-поле
        unset(
            $data['id'],
            $data['role']
        );

        $columns = array_keys($data);
        $placeholders = array_map(
            static fn (string $col): string => ':' . $col,
            $columns
        );

        $sql = sprintf(
            'INSERT INTO users (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    /**
     * Сохранение существующего пользователя (UPDATE).
     * Если id нет — считаем, что это новый пользователь и вызываем add().
     */
    public function save(User $user): void
    {
        $data = $user->toArray();

        // В UPDATE тоже не трогаем поле role — его нет в таблице
        unset($data['role']);

        $id = $data['id'] ?? null;

        if ($id === null) {
            $this->add($user);
            return;
        }

        // Обновляем все поля, кроме id
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
            'UPDATE users SET %s WHERE id = :id',
            implode(', ', $assignments)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }
}

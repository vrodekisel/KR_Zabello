<?php

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

    public function save(User $user): User
    {
        $data = $user->toArray();
        // ожидаем, что в массиве есть ключ 'id'
        $id = $data['id'] ?? null;

        if ($id === null) {
            // INSERT
            unset($data['id']);

            $columns = array_keys($data);
            $placeholders = array_map(
                fn(string $col): string => ':' . $col,
                $columns
            );

            $sql = sprintf(
                'INSERT INTO users (%s) VALUES (%s)',
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $newId = (int)$this->pdo->lastInsertId();
            $data['id'] = $newId;

            return User::fromArray($data);
        }

        // UPDATE
        $columns = array_keys($data);
        $columns = array_filter($columns, fn(string $col): bool => $col !== 'id');

        $assignments = array_map(
            fn(string $col): string => sprintf('%s = :%s', $col, $col),
            $columns
        );

        $sql = sprintf(
            'UPDATE users SET %s WHERE id = :id',
            implode(', ', $assignments)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $user;
    }
}

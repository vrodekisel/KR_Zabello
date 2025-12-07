<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

class User
{
    public const ROLE_PLAYER = 'player';
    public const ROLE_ADMIN  = 'admin';

    private ?int $id;
    private string $username;
    private string $passwordHash;
    private string $role;
    private bool $isBanned;
    private DateTimeImmutable $createdAt;

    public function __construct(
        ?int $id,
        string $username,
        string $passwordHash,
        string $role,
        bool $isBanned,
        DateTimeImmutable $createdAt
    ) {
        $this->assertValidRole($role);

        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->isBanned = $isBanned;
        $this->createdAt = $createdAt;
    }

    private function assertValidRole(string $role): void
    {
        if (!\in_array($role, [self::ROLE_PLAYER, self::ROLE_ADMIN], true)) {
            throw new \InvalidArgumentException('Invalid user role');
        }
    }

    /**
     * Сборка сущности из строки таблицы users.
     *
     * Ожидаемые ключи: id, username, password_hash, is_banned, created_at, (опционально) role.
     */
    public static function fromArray(array $row): self
    {
        $id = isset($row['id']) ? (int) $row['id'] : null;

        $username     = (string) ($row['username'] ?? '');
        $passwordHash = (string) ($row['password_hash'] ?? '');

        // В схеме пока нет колонки role, поэтому по умолчанию считаем, что это обычный игрок.
        $role = isset($row['role']) && $row['role'] !== ''
            ? (string) $row['role']
            : self::ROLE_PLAYER;

        $isBanned = isset($row['is_banned']) ? ((int) $row['is_banned'] === 1) : false;

        $createdAtRaw = $row['created_at'] ?? null;
        $createdAt = $createdAtRaw
            ? new DateTimeImmutable((string) $createdAtRaw)
            : new DateTimeImmutable('now');

        return new self(
            $id,
            $username,
            $passwordHash,
            $role,
            $isBanned,
            $createdAt
        );
    }

    /**
     * Представление сущности в виде массива для INSERT/UPDATE в таблицу users.
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'password_hash' => $this->passwordHash,
            // Колонки role может не быть — репозиторий сам решит, использовать её или нет.
            'role'          => $this->role,
            'is_banned'     => $this->isBanned ? 1 : 0,
            'created_at'    => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBanned(): bool
    {
        return $this->isBanned;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function ban(): void
    {
        $this->isBanned = true;
    }

    public function unban(): void
    {
        $this->isBanned = false;
    }
}

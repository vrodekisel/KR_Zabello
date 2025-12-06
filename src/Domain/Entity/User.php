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

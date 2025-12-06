<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepository
{
    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function add(User $user): void;

    public function save(User $user): void;
}

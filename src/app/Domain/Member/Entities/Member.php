<?php

declare(strict_types=1);

namespace App\Domain\Member\Entities;

use DateTimeImmutable;

final class Member
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $email,
        private readonly DateTimeImmutable $createdAt,
        private readonly DateTimeImmutable $updatedAt,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

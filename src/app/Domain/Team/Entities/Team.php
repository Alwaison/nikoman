<?php

declare(strict_types=1);

namespace App\Domain\Team\Entities;

use DateTimeImmutable;

final class Team
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly ?string $description,
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

    public function description(): ?string
    {
        return $this->description;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(string $name, ?string $description, DateTimeImmutable $updatedAt): self
    {
        return new self(
            id: $this->id,
            name: $name,
            description: $description,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
        );
    }
}

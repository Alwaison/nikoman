<?php

declare(strict_types=1);

namespace App\Application\Member\Commands;

final readonly class CreateMember
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}

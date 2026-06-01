<?php

declare(strict_types=1);

namespace App\Application\Member\Commands\UpdateMember;

final class UpdateMemberCommand
{
    public function __construct(
        public readonly string $memberId,
        public readonly string $name,
        public readonly string $email,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Application\Member\Commands\DeleteMember;

final class DeleteMemberCommand
{
    public function __construct(
        public readonly string $memberId,
    ) {}
}

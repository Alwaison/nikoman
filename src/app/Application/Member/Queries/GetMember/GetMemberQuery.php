<?php

declare(strict_types=1);

namespace App\Application\Member\Queries\GetMember;

final class GetMemberQuery
{
    public function __construct(
        public readonly string $memberId,
    ) {}
}

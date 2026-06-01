<?php

declare(strict_types=1);

namespace App\Application\Member\Queries\ListMembers;

final class ListMembersQuery
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $name = null,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Domain\Member\Repositories;

use App\Domain\Member\Entities\Member;

interface MemberRepositoryInterface
{
    public function save(Member $member): void;

    public function findById(string $id): ?Member;
}

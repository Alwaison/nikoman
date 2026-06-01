<?php

declare(strict_types=1);

namespace App\Domain\Member\Repositories;

use App\Domain\Member\Entities\Member;

interface MemberRepositoryInterface
{
    public function findById(string $id): ?Member;

    public function save(Member $member): Member;
}

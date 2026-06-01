<?php

declare(strict_types=1);

namespace App\Domain\Member\Repositories;

use App\Domain\Member\Entities\Member;
use App\Domain\Shared\ValueObjects\PaginatedResult;

interface MemberRepositoryInterface
{
    public function save(Member $member): void;

    public function findById(string $id): ?Member;

    public function delete(string $id): void;

    /** @return PaginatedResult<Member> */
    public function paginate(int $page, int $perPage, ?string $name = null): PaginatedResult;
}

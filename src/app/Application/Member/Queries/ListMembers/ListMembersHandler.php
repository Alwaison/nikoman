<?php

declare(strict_types=1);

namespace App\Application\Member\Queries\ListMembers;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use App\Domain\Shared\ValueObjects\PaginatedResult;

final class ListMembersHandler
{
    public function __construct(
        private readonly MemberRepositoryInterface $repository,
    ) {}

    /** @return PaginatedResult<Member> */
    public function handle(ListMembersQuery $query): PaginatedResult
    {
        return $this->repository->paginate($query->page, $query->perPage, $query->name);
    }
}

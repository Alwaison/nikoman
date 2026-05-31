<?php

declare(strict_types=1);

namespace App\Application\Member\Queries;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Repositories\MemberRepositoryInterface;

final class GetMemberHandler
{
    public function __construct(
        private readonly MemberRepositoryInterface $repository,
    ) {}

    public function handle(GetMember $query): ?Member
    {
        return $this->repository->findById($query->memberId);
    }
}

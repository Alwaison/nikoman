<?php

declare(strict_types=1);

namespace App\Application\Member\Queries\GetMember;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Exceptions\MemberNotFoundException;
use App\Domain\Member\Repositories\MemberRepositoryInterface;

final class GetMemberHandler
{
    public function __construct(
        private readonly MemberRepositoryInterface $repository,
    ) {}

    public function handle(GetMemberQuery $query): Member
    {
        return $this->repository->findById($query->memberId)
            ?? throw MemberNotFoundException::withId($query->memberId);
    }
}

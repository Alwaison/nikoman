<?php

declare(strict_types=1);

namespace App\Application\Member\Commands\CreateMember;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class CreateMemberHandler
{
    public function __construct(
        private readonly MemberRepositoryInterface $repository,
    ) {}

    public function handle(CreateMemberCommand $command): Member
    {
        $now = CarbonImmutable::now();

        $member = new Member(
            id: (string) Str::uuid(),
            name: $command->name,
            email: $command->email,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->repository->save($member);

        return $member;
    }
}

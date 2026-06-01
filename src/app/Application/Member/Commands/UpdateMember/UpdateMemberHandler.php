<?php

declare(strict_types=1);

namespace App\Application\Member\Commands\UpdateMember;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Exceptions\MemberNotFoundException;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use DateTimeImmutable;

final class UpdateMemberHandler
{
    public function __construct(
        private readonly MemberRepositoryInterface $repository,
    ) {}

    public function handle(UpdateMemberCommand $command): Member
    {
        $member = $this->repository->findById($command->memberId)
            ?? throw MemberNotFoundException::withId($command->memberId);

        $updated = $member->update(
            name: $command->name,
            email: $command->email,
            updatedAt: new DateTimeImmutable,
        );

        $this->repository->save($updated);

        return $updated;
    }
}

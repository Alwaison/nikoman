<?php

declare(strict_types=1);

namespace App\Application\Member\Commands;

use App\Application\Shared\UuidGeneratorInterface;
use App\Domain\Member\Entities\Member;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use DateTimeImmutable;

final class CreateMemberHandler
{
    public function __construct(
        private readonly MemberRepositoryInterface $repository,
        private readonly UuidGeneratorInterface $uuidGenerator,
    ) {}

    public function handle(CreateMember $command): Member
    {
        $now = new DateTimeImmutable;

        $member = new Member(
            id: $this->uuidGenerator->generate(),
            name: $command->name,
            email: $command->email,
            createdAt: $now,
            updatedAt: $now,
        );

        return $this->repository->save($member);
    }
}

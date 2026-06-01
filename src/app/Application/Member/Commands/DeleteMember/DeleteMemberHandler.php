<?php

declare(strict_types=1);

namespace App\Application\Member\Commands\DeleteMember;

use App\Domain\Member\Exceptions\MemberNotFoundException;
use App\Domain\Member\Repositories\MemberRepositoryInterface;

final class DeleteMemberHandler
{
    public function __construct(
        private readonly MemberRepositoryInterface $repository,
    ) {}

    public function handle(DeleteMemberCommand $command): void
    {
        if ($this->repository->findById($command->memberId) === null) {
            throw MemberNotFoundException::withId($command->memberId);
        }

        $this->repository->delete($command->memberId);
    }
}

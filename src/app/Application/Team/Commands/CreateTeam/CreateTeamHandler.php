<?php

declare(strict_types=1);

namespace App\Application\Team\Commands\CreateTeam;

use App\Domain\Team\Entities\Team;
use App\Domain\Team\Repositories\TeamRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class CreateTeamHandler
{
    public function __construct(
        private readonly TeamRepositoryInterface $repository,
    ) {}

    public function handle(CreateTeamCommand $command): Team
    {
        $now = CarbonImmutable::now();

        $team = new Team(
            id: (string) Str::uuid(),
            name: $command->name,
            description: $command->description,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->repository->save($team);

        return $team;
    }
}

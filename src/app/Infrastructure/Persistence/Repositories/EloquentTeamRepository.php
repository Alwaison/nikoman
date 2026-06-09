<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Team\Entities\Team;
use App\Domain\Team\Repositories\TeamRepositoryInterface;
use App\Infrastructure\Persistence\Models\TeamModel;
use DateTimeImmutable;

final class EloquentTeamRepository implements TeamRepositoryInterface
{
    public function save(Team $team): void
    {
        TeamModel::query()->updateOrCreate(
            ['id' => $team->id()],
            [
                'name' => $team->name(),
                'description' => $team->description(),
                'created_at' => $team->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => $team->updatedAt()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function findById(string $id): ?Team
    {
        $model = TeamModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    private function toEntity(TeamModel $model): Team
    {
        return new Team(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}

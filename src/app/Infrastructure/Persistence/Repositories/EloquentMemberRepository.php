<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use App\Infrastructure\Persistence\Models\MemberModel;
use DateTimeImmutable;

final class EloquentMemberRepository implements MemberRepositoryInterface
{
    public function save(Member $member): Member
    {
        $model = MemberModel::create([
            'id' => $member->id(),
            'name' => $member->name(),
            'email' => $member->email(),
        ]);

        $createdAt = $model->created_at ?? throw new \RuntimeException("Member {$model->id} has null created_at.");
        $updatedAt = $model->updated_at ?? throw new \RuntimeException("Member {$model->id} has null updated_at.");

        return new Member(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            createdAt: DateTimeImmutable::createFromInterface($createdAt),
            updatedAt: DateTimeImmutable::createFromInterface($updatedAt),
        );
    }

    public function findById(string $id): ?Member
    {
        $model = MemberModel::find($id);

        if ($model === null) {
            return null;
        }

        $createdAt = $model->created_at ?? throw new \RuntimeException("Member {$model->id} has null created_at.");
        $updatedAt = $model->updated_at ?? throw new \RuntimeException("Member {$model->id} has null updated_at.");

        return new Member(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            createdAt: DateTimeImmutable::createFromInterface($createdAt),
            updatedAt: DateTimeImmutable::createFromInterface($updatedAt),
        );
    }
}

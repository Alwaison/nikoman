<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use App\Infrastructure\Persistence\Models\MemberModel;
use DateTimeImmutable;

final class EloquentMemberRepository implements MemberRepositoryInterface
{
    public function save(Member $member): void
    {
        MemberModel::updateOrCreate(
            ['id' => $member->id()],
            [
                'name' => $member->name(),
                'email' => $member->email(),
                'created_at' => $member->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => $member->updatedAt()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function findById(string $id): ?Member
    {
        $model = MemberModel::find($id);

        if ($model === null) {
            return null;
        }

        return new Member(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Exceptions\DuplicateEmailException;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Infrastructure\Persistence\Models\MemberModel;
use DateTimeImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

final class EloquentMemberRepository implements MemberRepositoryInterface
{
    public function save(Member $member): void
    {
        try {
            MemberModel::query()->updateOrCreate(
                ['id' => $member->id()],
                [
                    'name' => $member->name(),
                    'email' => $member->email(),
                    'created_at' => $member->createdAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $member->updatedAt()->format('Y-m-d H:i:s'),
                ],
            );
        } catch (UniqueConstraintViolationException) {
            throw DuplicateEmailException::forEmail($member->email());
        }
    }

    public function findById(string $id): ?Member
    {
        $model = MemberModel::query()->find($id);

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

    public function delete(string $id): void
    {
        MemberModel::query()->whereKey($id)->delete();
    }

    /** @return PaginatedResult<Member> */
    public function paginate(int $page, int $perPage, ?string $name = null): PaginatedResult
    {
        $query = MemberModel::query();

        if ($name !== null) {
            $query->where('name', 'ilike', '%'.addcslashes($name, '%_\\').'%');
        }

        $paginator = $query
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = array_map(
            fn (MemberModel $model): Member => new Member(
                id: $model->id,
                name: $model->name,
                email: $model->email,
                createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
                updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
            ),
            $paginator->items(),
        );

        return new PaginatedResult(
            items: $items,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}

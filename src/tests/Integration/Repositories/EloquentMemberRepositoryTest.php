<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use App\Domain\Member\Entities\Member;
use App\Domain\Member\Exceptions\DuplicateEmailException;
use App\Infrastructure\Persistence\Repositories\EloquentMemberRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EloquentMemberRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentMemberRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentMemberRepository;
    }

    public function test_save_throws_duplicate_email_exception_when_email_already_taken(): void
    {
        $now = CarbonImmutable::now();

        $this->repository->save(new Member(
            id: (string) Str::uuid(),
            name: 'Alice',
            email: 'alice@example.com',
            createdAt: $now,
            updatedAt: $now,
        ));

        $this->expectException(DuplicateEmailException::class);

        // Simulates the race condition: a second member with the same email
        // bypasses the application-layer validation and hits the DB constraint.
        $this->repository->save(new Member(
            id: (string) Str::uuid(),
            name: 'Bob',
            email: 'alice@example.com',
            createdAt: $now,
            updatedAt: $now,
        ));
    }

    public function test_duplicate_email_exception_carries_the_conflicting_email(): void
    {
        $now = CarbonImmutable::now();
        $email = 'conflict@example.com';

        $this->repository->save(new Member(
            id: (string) Str::uuid(),
            name: 'First',
            email: $email,
            createdAt: $now,
            updatedAt: $now,
        ));

        try {
            $this->repository->save(new Member(
                id: (string) Str::uuid(),
                name: 'Second',
                email: $email,
                createdAt: $now,
                updatedAt: $now,
            ));
            $this->fail('DuplicateEmailException was not thrown.');
        } catch (DuplicateEmailException $e) {
            $this->assertStringContainsString($email, $e->getMessage());
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Member\Commands;

use App\Application\Member\Commands\UpdateMember\UpdateMemberCommand;
use App\Application\Member\Commands\UpdateMember\UpdateMemberHandler;
use App\Domain\Member\Entities\Member;
use App\Domain\Member\Exceptions\MemberNotFoundException;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UpdateMemberHandlerTest extends TestCase
{
    private MemberRepositoryInterface $repository;

    private UpdateMemberHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MemberRepositoryInterface::class);
        $this->handler = new UpdateMemberHandler($this->repository);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow(null);
    }

    public function test_it_returns_updated_member_with_new_name_and_email(): void
    {
        $this->repository->method('findById')->willReturn($this->aMember());
        $this->repository->method('save');

        $command = new UpdateMemberCommand(
            memberId: '550e8400-e29b-41d4-a716-446655440001',
            name: 'New Name',
            email: 'new@example.com',
        );

        $result = $this->handler->handle($command);

        $this->assertSame('New Name', $result->name());
        $this->assertSame('new@example.com', $result->email());
    }

    public function test_it_preserves_id_and_created_at_after_update(): void
    {
        $original = $this->aMember();
        $this->repository->method('findById')->willReturn($original);
        $this->repository->method('save');

        $result = $this->handler->handle(new UpdateMemberCommand(
            memberId: $original->id(),
            name: 'Other',
            email: 'other@example.com',
        ));

        $this->assertSame($original->id(), $result->id());
        $this->assertEquals($original->createdAt(), $result->createdAt());
    }

    public function test_it_sets_updated_at_to_now(): void
    {
        CarbonImmutable::setTestNow('2024-06-01 15:00:00');
        $this->repository->method('findById')->willReturn($this->aMember());
        $this->repository->method('save');

        $result = $this->handler->handle(new UpdateMemberCommand(
            memberId: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Name',
            email: 'email@example.com',
        ));

        $this->assertEquals(new DateTimeImmutable('2024-06-01 15:00:00'), $result->updatedAt());
    }

    public function test_it_persists_the_updated_member(): void
    {
        $this->repository->method('findById')->willReturn($this->aMember());

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn ($m) => $m->name() === 'New Name' && $m->email() === 'new@example.com'));

        $this->handler->handle(new UpdateMemberCommand(
            memberId: '550e8400-e29b-41d4-a716-446655440001',
            name: 'New Name',
            email: 'new@example.com',
        ));
    }

    public function test_it_throws_not_found_when_member_does_not_exist(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(MemberNotFoundException::class);

        $this->handler->handle(new UpdateMemberCommand(
            memberId: 'non-existent-id',
            name: 'Name',
            email: 'email@example.com',
        ));
    }

    public function test_it_does_not_persist_when_member_does_not_exist(): void
    {
        $this->repository->method('findById')->willReturn(null);
        $this->repository->expects($this->never())->method('save');

        try {
            $this->handler->handle(new UpdateMemberCommand(
                memberId: 'non-existent-id',
                name: 'Name',
                email: 'email@example.com',
            ));
        } catch (MemberNotFoundException) {
        }
    }

    private function aMember(): Member
    {
        return new Member(
            id: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: new DateTimeImmutable('2024-01-01 00:00:00'),
            updatedAt: new DateTimeImmutable('2024-01-01 00:00:00'),
        );
    }
}

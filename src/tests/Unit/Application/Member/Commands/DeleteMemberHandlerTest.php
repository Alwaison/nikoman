<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Member\Commands;

use App\Application\Member\Commands\DeleteMember\DeleteMemberCommand;
use App\Application\Member\Commands\DeleteMember\DeleteMemberHandler;
use App\Domain\Member\Entities\Member;
use App\Domain\Member\Exceptions\MemberNotFoundException;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeleteMemberHandlerTest extends TestCase
{
    private MemberRepositoryInterface $repository;

    private DeleteMemberHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MemberRepositoryInterface::class);
        $this->handler = new DeleteMemberHandler($this->repository);
    }

    public function test_it_deletes_the_member_by_id(): void
    {
        $this->repository->method('findById')->willReturn($this->aMember());

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with('550e8400-e29b-41d4-a716-446655440001');

        $this->handler->handle(new DeleteMemberCommand('550e8400-e29b-41d4-a716-446655440001'));
    }

    public function test_it_throws_not_found_when_member_does_not_exist(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(MemberNotFoundException::class);

        $this->handler->handle(new DeleteMemberCommand('non-existent-id'));
    }

    public function test_it_does_not_delete_when_member_does_not_exist(): void
    {
        $this->repository->method('findById')->willReturn(null);
        $this->repository->expects($this->never())->method('delete');

        try {
            $this->handler->handle(new DeleteMemberCommand('non-existent-id'));
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

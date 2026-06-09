<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Member\Queries;

use App\Application\Member\Queries\GetMember\GetMemberHandler;
use App\Application\Member\Queries\GetMember\GetMemberQuery;
use App\Domain\Member\Entities\Member;
use App\Domain\Member\Exceptions\MemberNotFoundException;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class GetMemberHandlerTest extends TestCase
{
    private MemberRepositoryInterface $repository;

    private GetMemberHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MemberRepositoryInterface::class);
        $this->handler = new GetMemberHandler($this->repository);
    }

    public function test_it_returns_the_found_member(): void
    {
        $member = $this->aMember();
        $this->repository->method('findById')->willReturn($member);

        $result = $this->handler->handle(new GetMemberQuery('550e8400-e29b-41d4-a716-446655440001'));

        $this->assertSame($member, $result);
    }

    public function test_it_queries_by_the_given_id(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with('550e8400-e29b-41d4-a716-446655440001')
            ->willReturn($this->aMember());

        $this->handler->handle(new GetMemberQuery('550e8400-e29b-41d4-a716-446655440001'));
    }

    public function test_it_throws_not_found_when_member_does_not_exist(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(MemberNotFoundException::class);

        $this->handler->handle(new GetMemberQuery('non-existent-id'));
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

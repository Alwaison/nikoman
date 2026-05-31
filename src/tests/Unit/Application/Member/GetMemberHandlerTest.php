<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Member;

use App\Application\Member\Queries\GetMember;
use App\Application\Member\Queries\GetMemberHandler;
use App\Domain\Member\Entities\Member;
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

    public function test_returns_member_when_found(): void
    {
        $memberId = '550e8400-e29b-41d4-a716-446655440000';
        $expected = new Member(
            id: $memberId,
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: new DateTimeImmutable('2024-01-15T10:00:00+00:00'),
            updatedAt: new DateTimeImmutable('2024-01-15T10:00:00+00:00'),
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($memberId)
            ->willReturn($expected);

        $result = $this->handler->handle(new GetMember($memberId));

        $this->assertSame($expected, $result);
    }

    public function test_returns_null_when_member_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $result = $this->handler->handle(new GetMember('non-existent-id'));

        $this->assertNull($result);
    }

    public function test_delegates_id_to_repository(): void
    {
        $memberId = 'abc-123';

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($memberId);

        $this->handler->handle(new GetMember($memberId));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Member;

use App\Application\Member\Commands\CreateMember;
use App\Application\Member\Commands\CreateMemberHandler;
use App\Application\Shared\UuidGeneratorInterface;
use App\Domain\Member\Entities\Member;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CreateMemberHandlerTest extends TestCase
{
    private MemberRepositoryInterface $repository;

    private UuidGeneratorInterface $uuidGenerator;

    private CreateMemberHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MemberRepositoryInterface::class);
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->handler = new CreateMemberHandler($this->repository, $this->uuidGenerator);
    }

    public function test_creates_member_with_generated_uuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $now = new DateTimeImmutable;
        $saved = new Member($uuid, 'Jane Doe', 'jane@example.com', $now, $now);

        $this->uuidGenerator->expects($this->once())->method('generate')->willReturn($uuid);
        $this->repository->expects($this->once())->method('save')
            ->with($this->callback(
                fn (Member $m) => $m->id() === $uuid
                    && $m->name() === 'Jane Doe'
                    && $m->email() === 'jane@example.com',
            ))
            ->willReturn($saved);

        $result = $this->handler->handle(new CreateMember('Jane Doe', 'jane@example.com'));

        $this->assertSame($uuid, $result->id());
        $this->assertSame('Jane Doe', $result->name());
        $this->assertSame('jane@example.com', $result->email());
    }

    public function test_delegates_name_and_email_to_repository(): void
    {
        $uuid = 'abc-uuid';
        $now = new DateTimeImmutable;

        $this->uuidGenerator->method('generate')->willReturn($uuid);
        $this->repository->expects($this->once())->method('save')
            ->with($this->callback(
                fn (Member $m) => $m->name() === 'John' && $m->email() === 'john@example.com',
            ))
            ->willReturn(new Member($uuid, 'John', 'john@example.com', $now, $now));

        $this->handler->handle(new CreateMember('John', 'john@example.com'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Member\Commands;

use App\Application\Member\Commands\CreateMember\CreateMemberCommand;
use App\Application\Member\Commands\CreateMember\CreateMemberHandler;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

final class CreateMemberHandlerTest extends TestCase
{
    private MemberRepositoryInterface $repository;

    private CreateMemberHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MemberRepositoryInterface::class);
        $this->handler = new CreateMemberHandler($this->repository);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow(null);
    }

    public function test_it_returns_member_with_name_and_email_from_command(): void
    {
        $this->repository->method('save');

        $command = new CreateMemberCommand(name: 'Jane Doe', email: 'jane@example.com');

        $member = $this->handler->handle($command);

        $this->assertSame('Jane Doe', $member->name());
        $this->assertSame('jane@example.com', $member->email());
    }

    public function test_it_sets_created_at_and_updated_at_to_now(): void
    {
        CarbonImmutable::setTestNow('2024-06-01 12:00:00');
        $this->repository->method('save');

        $member = $this->handler->handle(new CreateMemberCommand('Jane', 'jane@example.com'));

        $this->assertEquals(new \DateTimeImmutable('2024-06-01 12:00:00'), $member->createdAt());
        $this->assertEquals($member->createdAt(), $member->updatedAt());
    }

    public function test_it_persists_the_member_to_the_repository(): void
    {
        $command = new CreateMemberCommand(name: 'Jane Doe', email: 'jane@example.com');

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn ($m) => $m->name() === 'Jane Doe' && $m->email() === 'jane@example.com'));

        $this->handler->handle($command);
    }

    public function test_it_assigns_a_non_empty_id_to_the_new_member(): void
    {
        $this->repository->method('save');

        $member = $this->handler->handle(new CreateMemberCommand('Jane', 'jane@example.com'));

        $this->assertNotEmpty($member->id());
    }
}

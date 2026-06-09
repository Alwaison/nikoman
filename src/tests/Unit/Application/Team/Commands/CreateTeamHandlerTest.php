<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Team\Commands;

use App\Application\Team\Commands\CreateTeam\CreateTeamCommand;
use App\Application\Team\Commands\CreateTeam\CreateTeamHandler;
use App\Domain\Team\Repositories\TeamRepositoryInterface;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CreateTeamHandlerTest extends TestCase
{
    private TeamRepositoryInterface $repository;

    private CreateTeamHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TeamRepositoryInterface::class);
        $this->handler = new CreateTeamHandler($this->repository);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow(null);
    }

    public function test_it_returns_team_with_name_and_description_from_command(): void
    {
        $this->repository->method('save');

        $team = $this->handler->handle(new CreateTeamCommand(
            name: 'Engineering',
            description: 'The core engineering team',
        ));

        $this->assertSame('Engineering', $team->name());
        $this->assertSame('The core engineering team', $team->description());
    }

    public function test_it_accepts_null_description(): void
    {
        $this->repository->method('save');

        $team = $this->handler->handle(new CreateTeamCommand(name: 'Engineering', description: null));

        $this->assertNull($team->description());
    }

    public function test_it_sets_created_at_and_updated_at_to_now(): void
    {
        CarbonImmutable::setTestNow('2024-06-01 12:00:00');
        $this->repository->method('save');

        $team = $this->handler->handle(new CreateTeamCommand(name: 'Engineering', description: null));

        $this->assertEquals(new DateTimeImmutable('2024-06-01 12:00:00'), $team->createdAt());
        $this->assertEquals($team->createdAt(), $team->updatedAt());
    }

    public function test_it_persists_the_team_to_the_repository(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                fn ($t) => $t->name() === 'Engineering' && $t->description() === 'Core team',
            ));

        $this->handler->handle(new CreateTeamCommand(name: 'Engineering', description: 'Core team'));
    }

    public function test_it_assigns_a_non_empty_id_to_the_new_team(): void
    {
        $this->repository->method('save');

        $team = $this->handler->handle(new CreateTeamCommand(name: 'Engineering', description: null));

        $this->assertNotEmpty($team->id());
    }
}

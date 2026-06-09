<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use App\Domain\Team\Entities\Team;
use App\Infrastructure\Persistence\Repositories\EloquentTeamRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EloquentTeamRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentTeamRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentTeamRepository;
    }

    public function test_save_persists_all_fields_to_the_database(): void
    {
        $now = CarbonImmutable::now()->startOfSecond();
        $team = $this->aTeam(name: 'Engineering', description: 'Core team', now: $now);

        $this->repository->save($team);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id(),
            'name' => 'Engineering',
            'description' => 'Core team',
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_save_persists_null_description(): void
    {
        $this->repository->save($this->aTeam(name: 'Engineering', description: null));

        $this->assertDatabaseHas('teams', ['name' => 'Engineering', 'description' => null]);
    }

    public function test_save_updates_existing_record_when_id_already_exists(): void
    {
        $id = (string) Str::uuid();
        $now = CarbonImmutable::now()->startOfSecond();

        $this->repository->save($this->aTeam(id: $id, name: 'Engineering', description: 'Old', now: $now));
        $this->repository->save($this->aTeam(id: $id, name: 'Platform', description: 'New', now: $now));

        $this->assertDatabaseCount('teams', 1);
        $this->assertDatabaseHas('teams', ['id' => $id, 'name' => 'Platform', 'description' => 'New']);
    }

    public function test_find_by_id_returns_team_with_correct_fields(): void
    {
        $now = CarbonImmutable::now()->startOfSecond();
        $team = $this->aTeam(name: 'Engineering', description: 'Core team', now: $now);
        $this->repository->save($team);

        $found = $this->repository->findById($team->id());

        $this->assertNotNull($found);
        $this->assertSame($team->id(), $found->id());
        $this->assertSame('Engineering', $found->name());
        $this->assertSame('Core team', $found->description());
        $this->assertEquals($now, $found->createdAt());
        $this->assertEquals($now, $found->updatedAt());
    }

    public function test_find_by_id_returns_null_description_correctly(): void
    {
        $team = $this->aTeam(name: 'Engineering', description: null);
        $this->repository->save($team);

        $found = $this->repository->findById($team->id());

        $this->assertNotNull($found);
        $this->assertNull($found->description());
    }

    public function test_find_by_id_returns_null_when_team_does_not_exist(): void
    {
        $result = $this->repository->findById((string) Str::uuid());

        $this->assertNull($result);
    }

    private function aTeam(
        string $name,
        ?string $description,
        ?string $id = null,
        ?CarbonImmutable $now = null,
    ): Team {
        $now ??= CarbonImmutable::now();

        return new Team(
            id: $id ?? (string) Str::uuid(),
            name: $name,
            description: $description,
            createdAt: $now,
            updatedAt: $now,
        );
    }
}

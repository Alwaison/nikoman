<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Team\Entities;

use App\Domain\Team\Entities\Team;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TeamTest extends TestCase
{
    public function test_it_exposes_its_properties(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $team = new Team(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Engineering',
            description: 'The core engineering team',
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $team->id());
        $this->assertSame('Engineering', $team->name());
        $this->assertSame('The core engineering team', $team->description());
        $this->assertSame($createdAt, $team->createdAt());
        $this->assertSame($updatedAt, $team->updatedAt());
    }

    public function test_description_can_be_null(): void
    {
        $team = new Team(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Engineering',
            description: null,
            createdAt: new DateTimeImmutable('2024-01-01 00:00:00'),
            updatedAt: new DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $this->assertNull($team->description());
    }

    public function test_update_returns_new_instance_with_updated_fields(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');
        $newUpdatedAt = new DateTimeImmutable('2024-06-01 12:00:00');

        $original = new Team(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Engineering',
            description: 'Old description',
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $updated = $original->update('Platform', 'New description', $newUpdatedAt);

        $this->assertSame('Platform', $updated->name());
        $this->assertSame('New description', $updated->description());
        $this->assertSame($newUpdatedAt, $updated->updatedAt());
    }

    public function test_update_preserves_id_and_created_at(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');

        $original = new Team(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Engineering',
            description: null,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $updated = $original->update('Platform', null, new DateTimeImmutable('2024-06-01 00:00:00'));

        $this->assertSame($original->id(), $updated->id());
        $this->assertSame($original->createdAt(), $updated->createdAt());
    }

    public function test_update_does_not_mutate_original(): void
    {
        $original = new Team(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Engineering',
            description: 'Old',
            createdAt: new DateTimeImmutable('2024-01-01 00:00:00'),
            updatedAt: new DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $updated = $original->update('Platform', 'New', new DateTimeImmutable('2024-06-01 00:00:00'));

        $this->assertNotSame($original, $updated);
        $this->assertSame('Engineering', $original->name());
        $this->assertSame('Old', $original->description());
    }
}

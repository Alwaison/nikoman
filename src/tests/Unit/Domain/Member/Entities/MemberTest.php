<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Member\Entities;

use App\Domain\Member\Entities\Member;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    public function test_it_exposes_its_properties(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $member = new Member(
            id: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440001', $member->id());
        $this->assertSame('Jane Doe', $member->name());
        $this->assertSame('jane@example.com', $member->email());
        $this->assertSame($createdAt, $member->createdAt());
        $this->assertSame($updatedAt, $member->updatedAt());
    }

    public function test_update_returns_new_instance_with_updated_fields(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');
        $newUpdatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $original = new Member(
            id: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $updated = $original->update('Jane Updated', 'jane.updated@example.com', $newUpdatedAt);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440001', $updated->id());
        $this->assertSame('Jane Updated', $updated->name());
        $this->assertSame('jane.updated@example.com', $updated->email());
        $this->assertSame($createdAt, $updated->createdAt());
        $this->assertSame($newUpdatedAt, $updated->updatedAt());
    }

    public function test_update_preserves_id_and_created_at(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');

        $original = new Member(
            id: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $updated = $original->update('Other Name', 'other@example.com', new DateTimeImmutable('2024-06-01 00:00:00'));

        $this->assertSame($original->id(), $updated->id());
        $this->assertSame($original->createdAt(), $updated->createdAt());
    }

    public function test_update_does_not_mutate_original(): void
    {
        $original = new Member(
            id: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: new DateTimeImmutable('2024-01-01 00:00:00'),
            updatedAt: new DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $updated = $original->update('Jane Updated', 'jane.updated@example.com', new DateTimeImmutable('2024-01-02 00:00:00'));

        $this->assertNotSame($original, $updated);
        $this->assertSame('Jane Doe', $original->name());
        $this->assertSame('jane@example.com', $original->email());
    }

    public function test_updated_at_can_differ_from_created_at(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');
        $updatedAt = new DateTimeImmutable('2024-06-01 12:00:00');

        $member = new Member(
            id: '550e8400-e29b-41d4-a716-446655440001',
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertNotSame($member->createdAt(), $member->updatedAt());
        $this->assertLessThan($member->updatedAt(), $member->createdAt());
    }
}

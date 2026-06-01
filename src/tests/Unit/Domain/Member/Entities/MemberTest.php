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
}

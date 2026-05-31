<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Member\Entities;

use App\Domain\Member\Entities\Member;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    private Member $member;

    protected function setUp(): void
    {
        $this->member = new Member(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Jane Doe',
            email: 'jane@example.com',
            createdAt: new DateTimeImmutable('2024-01-15T10:00:00+00:00'),
            updatedAt: new DateTimeImmutable('2024-01-16T12:00:00+00:00'),
        );
    }

    public function test_id_is_returned(): void
    {
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $this->member->id());
    }

    public function test_name_is_returned(): void
    {
        $this->assertSame('Jane Doe', $this->member->name());
    }

    public function test_email_is_returned(): void
    {
        $this->assertSame('jane@example.com', $this->member->email());
    }

    public function test_created_at_is_returned(): void
    {
        $this->assertEquals(new DateTimeImmutable('2024-01-15T10:00:00+00:00'), $this->member->createdAt());
    }

    public function test_updated_at_is_returned(): void
    {
        $this->assertEquals(new DateTimeImmutable('2024-01-16T12:00:00+00:00'), $this->member->updatedAt());
    }

    public function test_member_is_immutable(): void
    {
        $reflection = new \ReflectionClass($this->member);

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly(), "Property {$property->getName()} must be readonly");
        }
    }
}

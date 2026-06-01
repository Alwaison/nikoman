<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_member_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->assertJsonFragment([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ]);

        $this->assertDatabaseHas('members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_returns_422_when_name_is_missing(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_email_is_missing(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_email_is_invalid(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_email_is_already_taken(): void
    {
        $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->postJson('/api/v1/members', [
            'name' => 'John Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }
}

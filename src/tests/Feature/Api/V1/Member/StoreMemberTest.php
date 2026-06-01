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

    public function test_response_id_is_a_valid_uuid(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $response->json('id'),
        );
    }

    public function test_created_at_and_updated_at_are_equal_on_creation(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->assertSame($response->json('created_at'), $response->json('updated_at'));
    }

    public function test_returns_422_when_name_is_missing(): void
    {
        $this->postJson('/api/v1/members', ['email' => 'jane@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_name_is_empty(): void
    {
        $this->postJson('/api/v1/members', ['name' => '', 'email' => 'jane@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_name_exceeds_255_characters(): void
    {
        $this->postJson('/api/v1/members', ['name' => str_repeat('a', 256), 'email' => 'jane@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_accepts_name_of_exactly_255_characters(): void
    {
        $this->postJson('/api/v1/members', ['name' => str_repeat('a', 255), 'email' => 'jane@example.com'])
            ->assertStatus(201);
    }

    public function test_returns_422_when_email_is_missing(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Jane Doe'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_email_is_invalid(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Jane Doe', 'email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_email_is_already_taken(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $this->postJson('/api/v1/members', ['name' => 'John Doe', 'email' => 'jane@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}

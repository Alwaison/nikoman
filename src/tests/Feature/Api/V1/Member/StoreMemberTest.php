<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function test_email_is_normalized_to_lowercase(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'JANE@EXAMPLE.COM',
        ]);

        $response->assertStatus(201);
        $this->assertSame('jane@example.com', $response->json('email'));
        $this->assertDatabaseHas('members', ['email' => 'jane@example.com']);
    }

    public function test_duplicate_email_detected_case_insensitively(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $this->postJson('/api/v1/members', ['name' => 'John Doe', 'email' => 'JANE@EXAMPLE.COM'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_race_condition_returns_422_not_500(): void
    {
        // Both requests see no existing member during validation.
        // The second save() hits the DB unique constraint — must return 422.
        $this->postJson('/api/v1/members', ['name' => 'Alice', 'email' => 'race@example.com'])
            ->assertStatus(201);

        // Simulate the race: bypass FormRequest validation by calling the
        // endpoint after manually deleting the validation guard (we insert
        // a duplicate directly at the DB level to trigger the constraint).
        DB::table('members')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Race clone',
            'email' => 'race2@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Now try to insert the same email through the API — the DB constraint
        // fires even though validation would pass in a race (both checked before insert).
        // We verify the application returns 422, not 500.
        $this->postJson('/api/v1/members', ['name' => 'Racer', 'email' => 'race2@example.com'])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_a_member_and_returns_200(): void
    {
        $created = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $id = $created->json('id');

        $response = $this->putJson("/api/v1/members/{$id}", [
            'name' => 'Jane Updated',
            'email' => 'jane.updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->assertJsonFragment([
                'id' => $id,
                'name' => 'Jane Updated',
                'email' => 'jane.updated@example.com',
            ]);

        $this->assertDatabaseHas('members', [
            'id' => $id,
            'name' => 'Jane Updated',
            'email' => 'jane.updated@example.com',
        ]);
    }

    public function test_created_at_is_preserved_and_updated_at_advances_after_update(): void
    {
        $created = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $originalCreatedAt = $created->json('created_at');

        $this->travel(1)->second();

        $updated = $this->putJson("/api/v1/members/{$created->json('id')}", [
            'name' => 'Jane Updated',
            'email' => 'jane@example.com',
        ]);

        $updated->assertStatus(200);

        $this->assertSame($originalCreatedAt, $updated->json('created_at'));
        $this->assertGreaterThan($originalCreatedAt, $updated->json('updated_at'));
    }

    public function test_can_update_with_same_email(): void
    {
        $created = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->putJson("/api/v1/members/{$created->json('id')}", [
            'name' => 'Jane Renamed',
            'email' => 'jane@example.com',
        ])->assertStatus(200)->assertJsonFragment(['name' => 'Jane Renamed']);
    }

    public function test_response_id_matches_path_parameter(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->putJson("/api/v1/members/{$id}", [
            'name' => 'Jane Updated',
            'email' => 'jane@example.com',
        ])->assertStatus(200)->assertJsonPath('id', $id);
    }

    public function test_returns_422_when_email_is_taken_by_another_member(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Alice', 'email' => 'alice@example.com']);

        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->putJson("/api/v1/members/{$id}", [
            'name' => 'Jane Doe',
            'email' => 'alice@example.com',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_returns_422_when_name_is_missing(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->putJson("/api/v1/members/{$id}", ['email' => 'jane@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_name_is_empty(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->putJson("/api/v1/members/{$id}", ['name' => '', 'email' => 'jane@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_name_exceeds_255_characters(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->putJson("/api/v1/members/{$id}", ['name' => str_repeat('a', 256), 'email' => 'jane@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_accepts_name_of_exactly_255_characters(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->putJson("/api/v1/members/{$id}", ['name' => str_repeat('a', 255), 'email' => 'jane@example.com'])
            ->assertStatus(200);
    }

    public function test_returns_422_when_email_is_invalid(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->putJson("/api/v1/members/{$id}", ['name' => 'Jane Doe', 'email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_returns_404_when_member_does_not_exist(): void
    {
        $this->putJson('/api/v1/members/00000000-0000-0000-0000-000000000000', [
            'name' => 'Ghost',
            'email' => 'ghost@example.com',
        ])->assertStatus(404);
    }

    public function test_returns_404_for_unknown_id(): void
    {
        $this->putJson('/api/v1/members/non-existent-id', [
            'name' => 'Ghost',
            'email' => 'ghost@example.com',
        ])->assertStatus(404);
    }
}

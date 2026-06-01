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

    public function test_can_update_with_same_email(): void
    {
        $created = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $id = $created->json('id');

        $response = $this->putJson("/api/v1/members/{$id}", [
            'name' => 'Jane Renamed',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Jane Renamed']);
    }

    public function test_returns_422_when_email_is_taken_by_another_member(): void
    {
        $this->postJson('/api/v1/members', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
        ]);

        $created = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->putJson("/api/v1/members/{$created->json('id')}", [
            'name' => 'Jane Doe',
            'email' => 'alice@example.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
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

    public function test_returns_404_when_member_does_not_exist(): void
    {
        $this->putJson('/api/v1/members/00000000-0000-0000-0000-000000000000', [
            'name' => 'Ghost',
            'email' => 'ghost@example.com',
        ])->assertStatus(404);
    }
}

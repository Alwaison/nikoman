<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShowMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_member_when_found(): void
    {
        $created = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $id = $created->json('id');

        $response = $this->getJson("/api/v1/members/{$id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->assertJsonFragment([
                'id' => $id,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ]);
    }

    public function test_response_timestamps_match_creation_data(): void
    {
        $created = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $fetched = $this->getJson("/api/v1/members/{$created->json('id')}");

        $this->assertSame($created->json('created_at'), $fetched->json('created_at'));
        $this->assertSame($created->json('updated_at'), $fetched->json('updated_at'));
    }

    public function test_returns_404_when_member_does_not_exist(): void
    {
        $this->getJson('/api/v1/members/00000000-0000-0000-0000-000000000000')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_returns_404_for_unknown_id(): void
    {
        $this->getJson('/api/v1/members/non-existent-id')
            ->assertStatus(404);
    }
}

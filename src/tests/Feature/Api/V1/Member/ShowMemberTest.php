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

    public function test_returns_404_when_member_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/members/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}

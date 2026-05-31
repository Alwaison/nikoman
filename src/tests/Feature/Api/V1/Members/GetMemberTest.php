<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Members;

use App\Infrastructure\Persistence\Models\MemberModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_member_when_found(): void
    {
        $member = MemberModel::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->getJson("/api/v1/members/{$member->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->assertJson([
                'id' => $member->id,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ]);
    }

    public function test_response_is_not_wrapped_in_data_key(): void
    {
        $member = MemberModel::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->getJson("/api/v1/members/{$member->id}");

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('data', $response->json());
    }

    public function test_returns_404_when_member_not_found(): void
    {
        $response = $this->getJson('/api/v1/members/550e8400-e29b-41d4-a716-446655440000');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Member not found.']);
    }

    public function test_returns_404_for_malformed_uuid(): void
    {
        $response = $this->getJson('/api/v1/members/not-a-valid-uuid');

        $response->assertStatus(404);
    }
}

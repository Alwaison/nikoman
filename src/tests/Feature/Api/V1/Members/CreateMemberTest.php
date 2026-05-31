<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Members;

use App\Infrastructure\Persistence\Models\MemberModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_member_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->assertJson([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ]);

        $this->assertDatabaseHas('members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_response_is_not_wrapped_in_data_key(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(201);
        $this->assertArrayNotHasKey('data', $response->json());
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

    public function test_returns_422_when_email_already_exists(): void
    {
        MemberModel::create(['name' => 'Existing', 'email' => 'jane@example.com']);

        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_response_id_is_a_valid_uuid(): void
    {
        $response = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id,
        );
    }
}

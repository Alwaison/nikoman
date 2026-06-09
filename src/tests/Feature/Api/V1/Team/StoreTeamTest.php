<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_team_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/teams', [
            'name' => 'Engineering',
            'description' => 'The core engineering team',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'description', 'created_at', 'updated_at'])
            ->assertJsonFragment([
                'name' => 'Engineering',
                'description' => 'The core engineering team',
            ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Engineering',
            'description' => 'The core engineering team',
        ]);
    }

    public function test_creates_a_team_without_description(): void
    {
        $response = $this->postJson('/api/v1/teams', ['name' => 'Engineering']);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Engineering'])
            ->assertJson(['description' => null]);

        $this->assertDatabaseHas('teams', ['name' => 'Engineering', 'description' => null]);
    }

    public function test_response_id_is_a_valid_uuid(): void
    {
        $response = $this->postJson('/api/v1/teams', ['name' => 'Engineering']);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $response->json('id'),
        );
    }

    public function test_created_at_and_updated_at_are_equal_on_creation(): void
    {
        $response = $this->postJson('/api/v1/teams', ['name' => 'Engineering']);

        $this->assertSame($response->json('created_at'), $response->json('updated_at'));
    }

    public function test_returns_422_when_name_is_missing(): void
    {
        $this->postJson('/api/v1/teams', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_name_is_empty(): void
    {
        $this->postJson('/api/v1/teams', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_name_exceeds_255_characters(): void
    {
        $this->postJson('/api/v1/teams', ['name' => str_repeat('a', 256)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_accepts_name_of_exactly_255_characters(): void
    {
        $this->postJson('/api/v1/teams', ['name' => str_repeat('a', 255)])
            ->assertStatus(201);
    }

    public function test_returns_422_when_description_exceeds_1000_characters(): void
    {
        $this->postJson('/api/v1/teams', ['name' => 'Engineering', 'description' => str_repeat('a', 1001)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_accepts_description_of_exactly_1000_characters(): void
    {
        $this->postJson('/api/v1/teams', ['name' => 'Engineering', 'description' => str_repeat('a', 1000)])
            ->assertStatus(201);
    }

    public function test_accepts_null_description_explicitly(): void
    {
        $this->postJson('/api/v1/teams', ['name' => 'Engineering', 'description' => null])
            ->assertStatus(201)
            ->assertJson(['description' => null]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DestroyMemberTest extends TestCase
{
    use RefreshDatabase;

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_deletes_a_member_and_returns_204(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->deleteJson("/api/v1/members/{$id}")
            ->assertStatus(204)
            ->assertNoContent();
    }

    public function test_deleted_member_is_removed_from_database(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->deleteJson("/api/v1/members/{$id}");

        $this->assertDatabaseMissing('members', ['id' => $id]);
    }

    public function test_response_body_is_empty(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $response = $this->deleteJson("/api/v1/members/{$id}");

        $this->assertEmpty($response->getContent());
    }

    // ── Lifecycle: show reflects the deletion ─────────────────────────────────

    public function test_deleted_member_is_no_longer_retrievable_via_show(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->deleteJson("/api/v1/members/{$id}");

        $this->getJson("/api/v1/members/{$id}")->assertStatus(404);
    }

    // ── Idempotency ───────────────────────────────────────────────────────────

    public function test_second_delete_on_same_id_returns_404(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->deleteJson("/api/v1/members/{$id}")->assertStatus(204);
        $this->deleteJson("/api/v1/members/{$id}")->assertStatus(404);
    }

    // ── Not found / bad input ─────────────────────────────────────────────────

    public function test_returns_404_when_member_does_not_exist(): void
    {
        $this->deleteJson('/api/v1/members/00000000-0000-0000-0000-000000000000')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_returns_404_for_non_uuid_id(): void
    {
        $this->deleteJson('/api/v1/members/not-a-uuid')
            ->assertStatus(404);
    }

    // ── Email freed after deletion ────────────────────────────────────────────

    public function test_deleted_member_email_can_be_reused(): void
    {
        $id = $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->json('id');

        $this->deleteJson("/api/v1/members/{$id}");

        $this->postJson('/api/v1/members', [
            'name' => 'Jane Doe II',
            'email' => 'jane@example.com',
        ])->assertStatus(201);
    }
}

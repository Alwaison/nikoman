<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IndexMemberTest extends TestCase
{
    use RefreshDatabase;

    // ── Response structure ────────────────────────────────────────────────────

    public function test_returns_200_with_data_and_meta_keys(): void
    {
        $this->getJson('/api/v1/members')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);
    }

    public function test_returns_empty_data_when_no_members_exist(): void
    {
        $response = $this->getJson('/api/v1/members')->assertStatus(200);

        $this->assertSame([], $response->json('data'));
        $this->assertSame(0, $response->json('meta.total'));
        $this->assertSame(1, $response->json('meta.last_page'));
    }

    // ── Member fields ─────────────────────────────────────────────────────────

    public function test_each_member_has_expected_fields(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $this->getJson('/api/v1/members')
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'created_at', 'updated_at']]]);
    }

    // ── Pagination: defaults ──────────────────────────────────────────────────

    public function test_default_per_page_is_15(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->postJson('/api/v1/members', ['name' => "Member {$i}", 'email' => "member{$i}@example.com"]);
        }

        $response = $this->getJson('/api/v1/members')->assertStatus(200);

        $this->assertCount(15, $response->json('data'));
        $this->assertSame(20, $response->json('meta.total'));
        $this->assertSame(15, $response->json('meta.per_page'));
        $this->assertSame(1, $response->json('meta.current_page'));
        $this->assertSame(2, $response->json('meta.last_page'));
    }

    // ── Pagination: page parameter ────────────────────────────────────────────

    public function test_second_page_returns_remaining_items(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->postJson('/api/v1/members', ['name' => "Member {$i}", 'email' => "member{$i}@example.com"]);
        }

        $response = $this->getJson('/api/v1/members?page=2&per_page=15')->assertStatus(200);

        $this->assertCount(5, $response->json('data'));
        $this->assertSame(2, $response->json('meta.current_page'));
    }

    public function test_page_beyond_last_returns_empty_data(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $response = $this->getJson('/api/v1/members?page=999')->assertStatus(200);

        $this->assertSame([], $response->json('data'));
        $this->assertSame(999, $response->json('meta.current_page'));
        $this->assertSame(1, $response->json('meta.total'));
    }

    // ── Pagination: per_page parameter ───────────────────────────────────────

    public function test_per_page_parameter_is_respected(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/v1/members', ['name' => "Member {$i}", 'email' => "member{$i}@example.com"]);
        }

        $response = $this->getJson('/api/v1/members?per_page=2')->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
        $this->assertSame(2, $response->json('meta.per_page'));
        $this->assertSame(3, $response->json('meta.last_page'));
    }

    public function test_per_page_of_100_is_accepted(): void
    {
        $this->getJson('/api/v1/members?per_page=100')->assertStatus(200);
    }

    // ── Pagination: validation ────────────────────────────────────────────────

    public function test_returns_422_when_page_is_zero(): void
    {
        $this->getJson('/api/v1/members?page=0')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['page']);
    }

    public function test_returns_422_when_per_page_is_zero(): void
    {
        $this->getJson('/api/v1/members?per_page=0')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_returns_422_when_per_page_exceeds_100(): void
    {
        $this->getJson('/api/v1/members?per_page=101')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_returns_422_when_page_is_not_an_integer(): void
    {
        $this->getJson('/api/v1/members?page=abc')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['page']);
    }

    // ── Ordering ──────────────────────────────────────────────────────────────

    public function test_members_are_ordered_by_creation_date_ascending(): void
    {
        $first = $this->postJson('/api/v1/members', ['name' => 'First', 'email' => 'first@example.com'])->json('id');

        $this->travel(1)->second();

        $second = $this->postJson('/api/v1/members', ['name' => 'Second', 'email' => 'second@example.com'])->json('id');

        $data = $this->getJson('/api/v1/members')->json('data');

        $this->assertSame($first, $data[0]['id']);
        $this->assertSame($second, $data[1]['id']);
    }

    public function test_pagination_is_stable_when_members_share_the_same_timestamp(): void
    {
        // All members inserted within the same second → created_at ties.
        // Without a deterministic tiebreaker (id) the DB can return rows in
        // any order, causing page boundaries to shift between requests and
        // producing skipped or duplicated rows.
        $ids = [];
        for ($i = 1; $i <= 4; $i++) {
            $ids[] = $this->postJson('/api/v1/members', [
                'name' => "Member {$i}",
                'email' => "member{$i}@example.com",
            ])->json('id');
        }

        $page1 = $this->getJson('/api/v1/members?per_page=2&page=1')->json('data');
        $page2 = $this->getJson('/api/v1/members?per_page=2&page=2')->json('data');

        $allIds = array_merge(
            array_column($page1, 'id'),
            array_column($page2, 'id'),
        );

        $this->assertCount(4, array_unique($allIds), 'No rows should be skipped or duplicated across pages.');
    }

    // ── Name filter ───────────────────────────────────────────────────────────

    public function test_filters_members_by_exact_name(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        $this->postJson('/api/v1/members', ['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $response = $this->getJson('/api/v1/members?name=Alice+Smith')->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Alice Smith', $response->json('data.0.name'));
    }

    public function test_filters_members_by_partial_name(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        $this->postJson('/api/v1/members', ['name' => 'Alice Johnson', 'email' => 'alicejohnson@example.com']);
        $this->postJson('/api/v1/members', ['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $response = $this->getJson('/api/v1/members?name=Alice')->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
        $this->assertSame(2, $response->json('meta.total'));
    }

    public function test_name_filter_is_case_insensitive(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        $this->postJson('/api/v1/members', ['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $this->assertCount(1, $this->getJson('/api/v1/members?name=alice')->json('data'));
        $this->assertCount(1, $this->getJson('/api/v1/members?name=ALICE')->json('data'));
        $this->assertCount(1, $this->getJson('/api/v1/members?name=AlIcE')->json('data'));
    }

    public function test_name_filter_returns_empty_when_no_match(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Alice Smith', 'email' => 'alice@example.com']);

        $response = $this->getJson('/api/v1/members?name=nobody')->assertStatus(200);

        $this->assertSame([], $response->json('data'));
        $this->assertSame(0, $response->json('meta.total'));
    }

    public function test_name_filter_matches_substring_in_middle_of_name(): void
    {
        $this->postJson('/api/v1/members', ['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        $this->postJson('/api/v1/members', ['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $response = $this->getJson('/api/v1/members?name=Smith')->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Alice Smith', $response->json('data.0.name'));
    }

    public function test_name_filter_meta_total_reflects_filtered_count(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/v1/members', ['name' => "Alice {$i}", 'email' => "alice{$i}@example.com"]);
        }
        $this->postJson('/api/v1/members', ['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $response = $this->getJson('/api/v1/members?name=Alice')->assertStatus(200);

        $this->assertSame(5, $response->json('meta.total'));
        $this->assertSame(1, $response->json('meta.last_page'));
    }

    public function test_name_filter_combined_with_pagination(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/v1/members', ['name' => "Alice {$i}", 'email' => "alice{$i}@example.com"]);
        }
        $this->postJson('/api/v1/members', ['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $page1 = $this->getJson('/api/v1/members?name=Alice&per_page=3&page=1')->assertStatus(200);
        $page2 = $this->getJson('/api/v1/members?name=Alice&per_page=3&page=2')->assertStatus(200);

        $this->assertCount(3, $page1->json('data'));
        $this->assertCount(2, $page2->json('data'));
        $this->assertSame(5, $page1->json('meta.total'));
        $this->assertSame(2, $page1->json('meta.last_page'));
    }

    public function test_returns_422_when_name_filter_exceeds_255_characters(): void
    {
        $this->getJson('/api/v1/members?name='.str_repeat('a', 256))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}

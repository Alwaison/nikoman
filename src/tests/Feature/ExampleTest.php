<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ExampleTest extends TestCase
{
    public function test_application_is_reachable(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
    }
}

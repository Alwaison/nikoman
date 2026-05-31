#!/usr/bin/env bash
# Scaffolds the clean architecture structure inside src/ after composer create-project.
# Usage: bash docker/scripts/scaffold.sh [target_dir]
set -euo pipefail

TARGET="${1:-src}"

echo "→ Creating architecture directories..."
mkdir -p \
  "$TARGET/app/Domain/Calendar/ValueObjects" \
  "$TARGET/app/Domain/Calendar/Entities" \
  "$TARGET/app/Domain/Calendar/Repositories" \
  "$TARGET/app/Domain/Team/Entities" \
  "$TARGET/app/Domain/Team/Repositories" \
  "$TARGET/app/Domain/Member/Entities" \
  "$TARGET/app/Domain/Member/Repositories" \
  "$TARGET/app/Application/Calendar/Commands" \
  "$TARGET/app/Application/Calendar/Queries" \
  "$TARGET/app/Application/Team/Commands" \
  "$TARGET/app/Application/Team/Queries" \
  "$TARGET/app/Application/Member/Commands" \
  "$TARGET/app/Application/Member/Queries" \
  "$TARGET/app/Infrastructure/Persistence/Models" \
  "$TARGET/app/Infrastructure/Persistence/Repositories" \
  "$TARGET/app/Http/Controllers/Api/V1" \
  "$TARGET/app/Http/Requests/Api/V1" \
  "$TARGET/app/Http/Resources/Api/V1" \
  "$TARGET/tests/Unit/Domain/Calendar/ValueObjects" \
  "$TARGET/tests/Unit/Domain/Calendar/Entities" \
  "$TARGET/tests/Unit/Domain/Team/Entities" \
  "$TARGET/tests/Unit/Domain/Member/Entities" \
  "$TARGET/tests/Unit/Application" \
  "$TARGET/tests/Feature/Api/V1" \
  "$TARGET/tests/Integration/Persistence"

# ─── Domain: Mood value object (first TDD green) ──────────────────────────────

echo "→ Writing Mood value object..."
cat > "$TARGET/app/Domain/Calendar/ValueObjects/Mood.php" << 'PHP'
<?php

declare(strict_types=1);

namespace App\Domain\Calendar\ValueObjects;

enum Mood: string
{
    case Happy = 'happy';
    case Neutral = 'neutral';
    case Sad = 'sad';

    public function label(): string
    {
        return match ($this) {
            Mood::Happy   => 'Happy',
            Mood::Neutral => 'Neutral',
            Mood::Sad     => 'Sad',
        };
    }

    public function isPositive(): bool
    {
        return $this === Mood::Happy;
    }

    public function isNegative(): bool
    {
        return $this === Mood::Sad;
    }
}
PHP

# ─── Unit test for Mood ───────────────────────────────────────────────────────

echo "→ Writing MoodTest (Unit)..."
cat > "$TARGET/tests/Unit/Domain/Calendar/ValueObjects/MoodTest.php" << 'PHP'
<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Calendar\ValueObjects;

use App\Domain\Calendar\ValueObjects\Mood;
use PHPUnit\Framework\TestCase;

final class MoodTest extends TestCase
{
    public function test_happy_mood_is_positive(): void
    {
        $mood = Mood::Happy;

        $this->assertTrue($mood->isPositive());
        $this->assertFalse($mood->isNegative());
    }

    public function test_sad_mood_is_negative(): void
    {
        $mood = Mood::Sad;

        $this->assertTrue($mood->isNegative());
        $this->assertFalse($mood->isPositive());
    }

    public function test_neutral_mood_is_neither_positive_nor_negative(): void
    {
        $mood = Mood::Neutral;

        $this->assertFalse($mood->isPositive());
        $this->assertFalse($mood->isNegative());
    }

    public function test_mood_has_human_readable_label(): void
    {
        $this->assertSame('Happy', Mood::Happy->label());
        $this->assertSame('Neutral', Mood::Neutral->label());
        $this->assertSame('Sad', Mood::Sad->label());
    }

    public function test_mood_can_be_created_from_its_string_value(): void
    {
        $this->assertSame(Mood::Happy, Mood::from('happy'));
        $this->assertSame(Mood::Neutral, Mood::from('neutral'));
        $this->assertSame(Mood::Sad, Mood::from('sad'));
    }

    public function test_mood_from_unknown_value_throws_value_error(): void
    {
        $this->expectException(\ValueError::class);

        Mood::from('unknown');
    }
}
PHP

# ─── Replace default Pest tests with PHPUnit style ───────────────────────────

echo "→ Replacing default tests with PHPUnit style..."
rm -f "$TARGET/tests/Pest.php"

cat > "$TARGET/tests/Unit/ExampleTest.php" << 'PHP'
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function test_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
PHP

cat > "$TARGET/tests/Feature/ExampleTest.php" << 'PHP'
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
PHP

# ─── Health check route placeholder ──────────────────────────────────────────

echo "→ Registering /api/v1/health route..."
# Append only if not already present
if ! grep -q "api/v1/health" "$TARGET/routes/api.php" 2>/dev/null; then
    cat >> "$TARGET/routes/api.php" << 'PHP'

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn (): JsonResponse => response()->json(['status' => 'ok']))->name('health');
});
PHP
fi

echo "→ Scaffold complete."

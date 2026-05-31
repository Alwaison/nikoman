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

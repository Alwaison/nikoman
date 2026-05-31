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
            Mood::Happy => 'Happy',
            Mood::Neutral => 'Neutral',
            Mood::Sad => 'Sad',
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

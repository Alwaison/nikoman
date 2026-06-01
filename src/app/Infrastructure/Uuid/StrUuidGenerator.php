<?php

declare(strict_types=1);

namespace App\Infrastructure\Uuid;

use App\Application\Shared\UuidGeneratorInterface;
use Illuminate\Support\Str;

final class StrUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return (string) Str::uuid();
    }
}

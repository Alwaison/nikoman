<?php

declare(strict_types=1);

namespace App\Domain\Member\Exceptions;

use RuntimeException;

final class MemberNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Member [{$id}] not found.");
    }
}

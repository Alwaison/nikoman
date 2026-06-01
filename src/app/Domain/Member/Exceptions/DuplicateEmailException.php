<?php

declare(strict_types=1);

namespace App\Domain\Member\Exceptions;

use RuntimeException;

final class DuplicateEmailException extends RuntimeException
{
    public static function forEmail(string $email): self
    {
        return new self("The email {$email} is already taken.");
    }
}

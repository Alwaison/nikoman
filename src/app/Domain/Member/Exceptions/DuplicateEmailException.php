<?php

declare(strict_types=1);

namespace App\Domain\Member\Exceptions;

use RuntimeException;

final class DuplicateEmailException extends RuntimeException
{
    public static function create(?\Throwable $previous = null): self
    {
        return new self('The email has already been taken.', 0, $previous);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

/**
 * @template T
 */
final class PaginatedResult
{
    /**
     * @param  array<T>  $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $currentPage,
        public readonly int $lastPage,
    ) {}
}

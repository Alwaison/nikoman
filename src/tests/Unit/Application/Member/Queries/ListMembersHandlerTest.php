<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Member\Queries;

use App\Application\Member\Queries\ListMembers\ListMembersHandler;
use App\Application\Member\Queries\ListMembers\ListMembersQuery;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use PHPUnit\Framework\TestCase;

final class ListMembersHandlerTest extends TestCase
{
    private MemberRepositoryInterface $repository;

    private ListMembersHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MemberRepositoryInterface::class);
        $this->handler = new ListMembersHandler($this->repository);
    }

    public function test_it_returns_paginated_result_from_repository(): void
    {
        $expected = new PaginatedResult(items: [], total: 0, perPage: 15, currentPage: 1, lastPage: 1);
        $this->repository->method('paginate')->willReturn($expected);

        $result = $this->handler->handle(new ListMembersQuery(page: 1, perPage: 15));

        $this->assertSame($expected, $result);
    }

    public function test_it_passes_page_and_per_page_to_repository(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('paginate')
            ->with(3, 25, null)
            ->willReturn(new PaginatedResult(items: [], total: 0, perPage: 25, currentPage: 3, lastPage: 1));

        $this->handler->handle(new ListMembersQuery(page: 3, perPage: 25));
    }

    public function test_it_passes_name_filter_to_repository(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('paginate')
            ->with(1, 15, 'Jane')
            ->willReturn(new PaginatedResult(items: [], total: 0, perPage: 15, currentPage: 1, lastPage: 1));

        $this->handler->handle(new ListMembersQuery(page: 1, perPage: 15, name: 'Jane'));
    }

    public function test_it_passes_null_name_when_not_provided(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('paginate')
            ->with(1, 15, null)
            ->willReturn(new PaginatedResult(items: [], total: 0, perPage: 15, currentPage: 1, lastPage: 1));

        $this->handler->handle(new ListMembersQuery(page: 1, perPage: 15));
    }
}

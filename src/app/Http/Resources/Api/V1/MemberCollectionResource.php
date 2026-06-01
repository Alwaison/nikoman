<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domain\Member\Entities\Member;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property PaginatedResult<Member> $resource */
final class MemberCollectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(
                fn (Member $member): array => (new MemberResource($member))->toArray($request),
                $this->resource->items,
            ),
            'meta' => [
                'total' => $this->resource->total,
                'per_page' => $this->resource->perPage,
                'current_page' => $this->resource->currentPage,
                'last_page' => $this->resource->lastPage,
            ],
        ];
    }
}

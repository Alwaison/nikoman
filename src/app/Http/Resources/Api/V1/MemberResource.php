<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domain\Member\Entities\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Member $resource */
final class MemberResource extends JsonResource
{
    public static $wrap = null;

    /** @return array<string, string> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id(),
            'name' => $this->resource->name(),
            'email' => $this->resource->email(),
            'created_at' => $this->resource->createdAt()->format(\DateTimeInterface::RFC3339),
            'updated_at' => $this->resource->updatedAt()->format(\DateTimeInterface::RFC3339),
        ];
    }
}

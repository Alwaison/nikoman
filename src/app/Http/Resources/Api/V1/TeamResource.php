<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domain\Team\Entities\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Team $resource */
final class TeamResource extends JsonResource
{
    public static $wrap = null;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id(),
            'name' => $this->resource->name(),
            'description' => $this->resource->description(),
            'created_at' => $this->resource->createdAt()->format(\DateTimeInterface::RFC3339),
            'updated_at' => $this->resource->updatedAt()->format(\DateTimeInterface::RFC3339),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domain\Member\Entities\Member;
use DateTimeInterface;
use Illuminate\Http\Request;

final class MemberResource extends ApiResource
{
    public function __construct(private readonly Member $member)
    {
        parent::__construct($member);
    }

    /** @return array<string, string> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->member->id(),
            'name' => $this->member->name(),
            'email' => $this->member->email(),
            'created_at' => $this->member->createdAt()->format(DateTimeInterface::ATOM),
            'updated_at' => $this->member->updatedAt()->format(DateTimeInterface::ATOM),
        ];
    }
}

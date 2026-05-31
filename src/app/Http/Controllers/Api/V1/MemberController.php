<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Member\Queries\GetMember;
use App\Application\Member\Queries\GetMemberHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class MemberController extends Controller
{
    public function __construct(
        private readonly GetMemberHandler $handler,
    ) {}

    public function show(string $memberId): MemberResource|JsonResponse
    {
        $member = $this->handler->handle(new GetMember($memberId));

        if ($member === null) {
            return response()->json(
                ['message' => 'Member not found.'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return new MemberResource($member);
    }
}

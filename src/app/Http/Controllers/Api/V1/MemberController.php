<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Member\Commands\CreateMember;
use App\Application\Member\Commands\CreateMemberHandler;
use App\Application\Member\Queries\GetMember;
use App\Application\Member\Queries\GetMemberHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateMemberRequest;
use App\Http\Resources\Api\V1\MemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class MemberController extends Controller
{
    public function __construct(
        private readonly GetMemberHandler $getMemberHandler,
        private readonly CreateMemberHandler $createMemberHandler,
    ) {}

    public function show(string $memberId): MemberResource|JsonResponse
    {
        $member = $this->getMemberHandler->handle(new GetMember($memberId));

        if ($member === null) {
            return response()->json(
                ['message' => 'Member not found.'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return new MemberResource($member);
    }

    public function store(CreateMemberRequest $request): JsonResponse
    {
        $member = $this->createMemberHandler->handle(
            new CreateMember(
                name: $request->name(),
                email: $request->email(),
            ),
        );

        return response()->json(
            (new MemberResource($member))->toArray($request),
            Response::HTTP_CREATED,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Member\Commands\CreateMember\CreateMemberCommand;
use App\Application\Member\Commands\CreateMember\CreateMemberHandler;
use App\Application\Member\Queries\GetMember\GetMemberHandler;
use App\Application\Member\Queries\GetMember\GetMemberQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMemberRequest;
use App\Http\Resources\Api\V1\MemberResource;
use Illuminate\Http\JsonResponse;

final class MemberController extends Controller
{
    public function __construct(
        private readonly CreateMemberHandler $createMemberHandler,
        private readonly GetMemberHandler $getMemberHandler,
    ) {}

    public function store(StoreMemberRequest $request): JsonResponse
    {
        $command = new CreateMemberCommand(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
        );

        $member = $this->createMemberHandler->handle($command);

        return (new MemberResource($member))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $memberId): JsonResponse
    {
        $member = $this->getMemberHandler->handle(new GetMemberQuery($memberId));

        return (new MemberResource($member))->response();
    }
}

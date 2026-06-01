<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Member\Commands\CreateMember\CreateMemberCommand;
use App\Application\Member\Commands\CreateMember\CreateMemberHandler;
use App\Application\Member\Commands\DeleteMember\DeleteMemberCommand;
use App\Application\Member\Commands\DeleteMember\DeleteMemberHandler;
use App\Application\Member\Commands\UpdateMember\UpdateMemberCommand;
use App\Application\Member\Commands\UpdateMember\UpdateMemberHandler;
use App\Application\Member\Queries\GetMember\GetMemberHandler;
use App\Application\Member\Queries\GetMember\GetMemberQuery;
use App\Application\Member\Queries\ListMembers\ListMembersHandler;
use App\Application\Member\Queries\ListMembers\ListMembersQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMembersRequest;
use App\Http\Requests\Api\V1\StoreMemberRequest;
use App\Http\Requests\Api\V1\UpdateMemberRequest;
use App\Http\Resources\Api\V1\MemberCollectionResource;
use App\Http\Resources\Api\V1\MemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class MemberController extends Controller
{
    public function __construct(
        private readonly ListMembersHandler $listMembersHandler,
        private readonly CreateMemberHandler $createMemberHandler,
        private readonly GetMemberHandler $getMemberHandler,
        private readonly UpdateMemberHandler $updateMemberHandler,
        private readonly DeleteMemberHandler $deleteMemberHandler,
    ) {}

    public function index(ListMembersRequest $request): JsonResponse
    {
        $result = $this->listMembersHandler->handle(
            new ListMembersQuery(
                page: $request->integer('page', 1),
                perPage: $request->integer('per_page', 15),
            )
        );

        return (new MemberCollectionResource($result))->response();
    }

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

    public function update(UpdateMemberRequest $request, string $memberId): JsonResponse
    {
        $command = new UpdateMemberCommand(
            memberId: $memberId,
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
        );

        $member = $this->updateMemberHandler->handle($command);

        return (new MemberResource($member))->response();
    }

    public function destroy(string $memberId): Response
    {
        $this->deleteMemberHandler->handle(new DeleteMemberCommand($memberId));

        return response()->noContent();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Team\Commands\CreateTeam\CreateTeamCommand;
use App\Application\Team\Commands\CreateTeam\CreateTeamHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTeamRequest;
use App\Http\Resources\Api\V1\TeamResource;
use Illuminate\Http\JsonResponse;

final class TeamController extends Controller
{
    public function __construct(
        private readonly CreateTeamHandler $createTeamHandler,
    ) {}

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->createTeamHandler->handle(new CreateTeamCommand(
            name: $request->string('name')->toString(),
            description: $request->input('description'),
        ));

        return (new TeamResource($team))
            ->response()
            ->setStatusCode(201);
    }
}

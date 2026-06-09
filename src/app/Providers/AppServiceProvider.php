<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Member\Repositories\MemberRepositoryInterface;
use App\Domain\Team\Repositories\TeamRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentMemberRepository;
use App\Infrastructure\Persistence\Repositories\EloquentTeamRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MemberRepositoryInterface::class, EloquentMemberRepository::class);
        $this->app->bind(TeamRepositoryInterface::class, EloquentTeamRepository::class);
    }

    public function boot(): void
    {
        //
    }
}

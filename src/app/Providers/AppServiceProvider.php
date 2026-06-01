<?php

namespace App\Providers;

<<<<<<< Updated upstream
=======
use App\Application\Shared\UuidGeneratorInterface;
use App\Domain\Member\Repositories\MemberRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentMemberRepository;
use App\Infrastructure\Uuid\StrUuidGenerator;
>>>>>>> Stashed changes
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
<<<<<<< Updated upstream
        //
=======
        $this->app->bind(MemberRepositoryInterface::class, EloquentMemberRepository::class);
        $this->app->bind(UuidGeneratorInterface::class, StrUuidGenerator::class);
>>>>>>> Stashed changes
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

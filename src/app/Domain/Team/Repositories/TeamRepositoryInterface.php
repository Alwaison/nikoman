<?php

declare(strict_types=1);

namespace App\Domain\Team\Repositories;

use App\Domain\Team\Entities\Team;

interface TeamRepositoryInterface
{
    public function save(Team $team): void;
}

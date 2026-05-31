<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class MemberModel extends Model
{
    use HasUuids;

    protected $table = 'members';

    protected $fillable = ['name', 'email'];

    public $incrementing = false;

    protected $keyType = 'string';
}

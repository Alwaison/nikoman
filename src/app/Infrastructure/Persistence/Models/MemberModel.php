<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class MemberModel extends Model
{
    protected $table = 'members';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'email'];
}

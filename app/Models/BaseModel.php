<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new ActiveScope());
    }
}

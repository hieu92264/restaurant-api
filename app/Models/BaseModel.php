<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new ActiveScope());
    }
}

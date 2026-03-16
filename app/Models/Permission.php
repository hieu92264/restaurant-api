<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends BaseModel
{
    protected $fillable = [
        'is_active',
        'name',
        'code',
        'remark',
        'url',
        'parent_id',
    ];

    protected $hidden = [];

    protected $casts = [];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Permission::class, 'parent_id', 'id');
    }
}

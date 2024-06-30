<?php

namespace SimpleCMS\Framework;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * 
 * @property ?\SimpleCMS\Framework\Models\Role $roles
 */
interface HasRole
{
    public function roles(): BelongsToMany;

    public function isSuper(): bool;

}

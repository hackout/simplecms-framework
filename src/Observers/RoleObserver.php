<?php

namespace SimpleCMS\Framework\Observers;

use Illuminate\Support\Facades\DB;
use SimpleCMS\Framework\Models\Role;

class RoleObserver
{
    

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        DB::table('roles_more')->where('role_id', $role->id)->delete();
    }

}

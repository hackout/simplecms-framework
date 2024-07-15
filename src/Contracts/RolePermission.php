<?php
namespace SimpleCMS\Framework\Contracts;

use Illuminate\Http\Request;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface RolePermission
{
    public function hasRole(string $role): bool;

    public function failRedirect(Request $request);
}
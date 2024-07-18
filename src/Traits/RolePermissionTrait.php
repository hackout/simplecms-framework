<?php
namespace SimpleCMS\Framework\Traits;

use SimpleCMS\Framework\HasRole;
use SimpleCMS\Framework\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 简易树型结构
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * 你可以定义超管的字段:
 *
 *   const SUPER_ID = 'my_is_super';
 * 
 * @use \Illuminate\Database\Eloquent\Model
 * @use \Illuminate\Database\Eloquent\Concerns\HasRelationships
 * 
 * @static string SUPER_ID
 *
 */
trait RolePermissionTrait
{

    public static function bootRolePermissionTrait()
    {
        static::deleting(function (HasRole $model) {
            $model->roles && $model->roles->detach();
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->/** @scrutinizer ignore-call */belongsToMany(Role::class, 'roles_more', 'model_id', 'role_id')->wherePivot('model_type', get_class($this));
    }

    /**
     * 检查权限
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string  $role
     * @return boolean
     */
    public function hasRole(string $role): bool
    {
        if ($this->{$this->getSuperColumnName()})
            return true;
        $allRoles = Role::getRolesByModel($this);
        $roleList = explode(',', $role);
        if (in_array('*', $roleList))
            return true;
        if (empty($roleList))
            return false;
        foreach ($roleList as $_role) {
            if (in_array($_role, $allRoles))
                return true;
        }
        return false;
    }

    public function isSuper(): bool
    {
        return $this->{$this->getSuperColumnName()} == true;
    }

    protected function getSuperColumnName(): string
    {
        // 使用类常量
        if (defined('self::SUPER_ID')) {
            return self::SUPER_ID;
        }

        return 'is_super';
    }
}
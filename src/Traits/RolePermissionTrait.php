<?php
namespace SimpleCMS\Framework\Traits;

use SimpleCMS\Framework\Models\Role;

/**
 * 简易树型结构
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * Usage:
 *
 * Model table must have parent_id table column.
 * In the model class definition:
 *
 *   use \SimpleCMS\Framework\Traits\SimpleTree;
 *
 * General access methods:
 *
 *   $model->getChildren(); // Returns children of this node
 *   $model->getChildCount(); // Returns number of all children.
 *   $model->getAllChildren(); // Returns all children of this node
 *   $model->getAllRoot(); // Returns all root level nodes (eager loaded)
 *   $model->getAll(); // Returns everything in correct order.
 *
 * Query builder methods:
 *
 *   $query->listsNested(); // Returns an indented array of key and value columns.
 *
 * 你可以定义超管的字段:
 *
 *   const SUPER_ID = 'my_is_super';
 */
trait RolePermissionTrait
{
    /**
     * 检查权限
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string  $role
     * @return boolean
     */
    public function checkRole(string $role): bool
    {
        if ($this->{$this->getSuperColumnName()})
            return true;
        $allRoles = Role::getRolesByModel($this);
        $roleList = explode(',', $role);
        if (in_array('*', $roleList))
            return true;
        if (!$roleList)
            return false;
        foreach ($roleList as $_role) {
            if (in_array($_role, $allRoles))
                return true;
        }
        return false;
    }

    protected function getSuperColumnName(): string
    {
        return defined('static::SUPER_ID') ? static::SUPER_ID : 'is_super';
    }
}
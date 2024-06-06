<?php
namespace SimpleCMS\Framework\Packages\Menu;

use Illuminate\Routing\Route;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Models\Menu as MenuModel;

/**
 * 菜单
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Menu
{

    /**
     * 通过路由获取菜单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Route      $route
     * @param  string|null      $type
     * @return ?MenuClass
     */
    public function getMenuTreeByRoute(Route $route, string $type = null): ?MenuClass
    {
        if (!$route->getName())
            return null;
        $menu = MenuModel::where(['is_valid' => true, 'url->name' => $route->getName()]);
        if ($type) {
            $menu->where('type', $type == 'backend' ? MenuModel::TYPE_BACKEND : MenuModel::TYPE_FRONTEND);
        }
        $current = $menu->first();
        $parent = $current->parent ?? false;
        $list = [$current];
        while ($parent) {
            $list[] = $parent;
            $parent = $parent->parent ?? false;
        }
        return $this->matchRoute(array_reverse($list));
    }

    /**
     * 重组菜单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array     $menus
     * @return MenuClass|null
     */
    protected function matchRoute(array $menus): ?MenuClass
    {
        if (!$menus || empty($menus[0]))
            return null;
        $menu = new MenuClass;
        $menu->name = $menus[0]->name;
        $menu->url = $menus[0]->url;
        $menu->is_show = $menus[0]->is_show;
        array_shift($menus);
        if ($menus) {
            $menu->children = $this->matchRoute($menus);
        }
        return $menu;
    }

    /**
     * 获取后台菜单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array<string>      $roles
     * @return Collection<MenuClass>
     */
    public function backendMenu(array $roles = ['*']): Collection
    {
        $menus = MenuModel::where(['type' => MenuModel::TYPE_BACKEND, 'is_valid' => true])->where(function ($query) {
            $query->whereNull('parent_id')
                ->orWhere('parent_id', 0);
        })->orderBy('sort_order', 'DESC')->get();

        return $this->checkRole($menus, $roles);
    }

    /**
     * 获取前台菜单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array<string>      $roles
     * @return Collection<MenuClass>
     */
    public function frontendMenu(array $roles = ['*']): Collection
    {
        $menus = MenuModel::where(['type' => MenuModel::TYPE_FRONTEND, 'is_valid' => true])->where(function ($query) {
            $query->whereNull('parent_id')
                ->orWhere('parent_id', 0);
        })->orderBy('sort_order', 'DESC')->get();

        return $this->checkRole($menus, $roles);
    }

    /**
     * 过滤权限并返回菜单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Collection $menus
     * @param  array<string>     $roles
     * @return Collection<MenuClass>
     */
    protected function checkRole(Collection $menus, array $roles): Collection
    {
        return $menus->filter(function (MenuModel $menu) use ($roles) {
            if (in_array('*', $roles))
                return true;
            return is_array($menu->url) && array_key_exists('name', $menu->url) && in_array($menu->url['name'], $roles);
        })->values()->map(function (MenuModel $menu) use ($roles) {
            $object = new MenuClass;
            $object->name = $menu->name;
            $object->url = $menu->url;
            $object->is_show = $menu->is_show;
            $object->children = $this->checkRole($menu->getAllChildren(), $roles)->toArray();
        });
    }
}
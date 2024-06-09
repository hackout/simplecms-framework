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
     * @return ?MenuClass
     */
    public function getMenuTreeByRoute(Route $route): ?MenuClass
    {
        // 获取当前路由信息
        $currentRouteName = $route->getName();
        if (!$currentRouteName)
        {
            return null;
        }
        $siblingList = collect([]);
        $breadcrumbs = [];
        $menu = MenuModel::where(['is_valid' => true, 'url->name' => $currentRouteName])->with('children')->first();
        
        if (!$menu) {
            return null;
        }
        $current = $this->matchRoute($menu,true,true);
        $siblingList->push($current);
        $parent = $menu->parent;
        if($parent)
        {
            /**
             * 获取同级菜单
             */
            $parent->children->filter(fn(MenuModel $item) => $item->id != $menu->id)
                             ->values()
                             ->each(fn(MenuModel $item)=>$siblingList->push($this->matchRoute($item,false,false)));
        }
        $current->siblings = $siblingList->sortByDesc(fn(MenuClass $item) => $item->sort_order)->values()->toArray();
        $breadcrumbs[] = $current;

        while($parent)
        {
            $breadcrumbs[] = $this->matchRoute($parent,true,false);
            $parent = $parent->parent;
        }
        $result = array_pop($breadcrumbs);
        while(count($breadcrumbs) > 0){
            $result = $this->deepMap($result,array_pop($breadcrumbs));
        }
        return $result;
    }

    /**
     * 深度赋值
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  MenuClass $result
     * @param  MenuClass $child
     * @return MenuClass
     */
    protected function deepMap(MenuClass $result,MenuClass $child):MenuClass
    {
        if(empty($result->child))
        {
            $result->child = $child;
        }else{
            $result->child = $this->deepMap($result->child,$child);
        }
        return $result;
    }

    /**
     * 转换菜单类
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  MenuModel     $menu
     * @param  bool          $current
     * @param  bool          $needChild
     * @return MenuClass|null
     */
    protected function matchRoute(MenuModel $menu,bool $current = false,bool $needChild = false): ?MenuClass
    {
        $menuClass = new MenuClass;
        $menuClass->name = $menu->name;
        $menuClass->url = $menu->url;
        $menuClass->icon = $menu->icon;
        $menuClass->sort_order = $menu->sort_order;
        $menuClass->current = $current;
        $menuClass->is_show = $menu->is_show;
        if($needChild)
        {
            $menuClass->children = $menu->children->map(fn(MenuModel $menu) => $this->matchRoute($menu))->toArray();
        }
        return $menuClass;
    }

    /**
     * 获取后台菜单
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array<string>      $roles
     * @return Collection<MenuClass>|\Illuminate\Support\Collection<MenuClass>
     */
    public function backendMenu(array $roles = ['*'])
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
     * @return Collection<MenuClass>|\Illuminate\Support\Collection<MenuClass>
     */
    public function frontendMenu(array $roles = ['*'])
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
     * @return Collection<MenuClass>|\Illuminate\Support\Collection<MenuClass>
     */
    protected function checkRole(Collection $menus, array $roles)
    {
        return $menus->filter(function (MenuModel $menu) use ($roles) {
            if (in_array('*', $roles))
                return true;
            return is_array($menu->url) && array_key_exists('name', $menu->url) && in_array($menu->url['name'], $roles);
        })->values()->map(function (MenuModel $menu) use ($roles) {
            $object = new MenuClass;
            $object->name = $menu->name;
            $object->url = $menu->url;
            $object->icon = $menu->icon;
            $object->is_show = $menu->is_show;
            $object->children = $this->checkRole($menu->getAllChildren(), $roles)->toArray();
            return $object;
        });
    }
}
<?php
namespace SimpleCMS\Framework\Packages\Menu;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use SimpleCMS\Framework\Models\Menu as MenuModel;

/**
 * 菜单
 */
class Menu
{
    /**
     * 通过路由获取菜单
     *
     * @param  Route $route
     * @return ?MenuClass
     */
    public function getMenuTreeByRoute(Route $route): ?MenuClass
    {
        $currentRouteName = $route->getName();
        if (empty($currentRouteName)) {
            return null;
        }

        $menu = MenuModel::where(['is_valid' => true, 'url->name' => $currentRouteName])
            ->where('parent_id', '!=', 0)->first();

        if (empty($menu)) {
            return null;
        }

        $current = $this->matchRoute($menu, true, true);
        $siblingList = collect(/** @scrutinizer ignore-type */[$current]);
        $breadcrumbs = [$current];

        $parent = $menu->parent;
        if (!empty($parent)) {
            $this->addSiblings($parent, $menu, $siblingList);
        }

        $current->siblings = $siblingList->sortByDesc(fn(MenuClass $item) => $item->sort_order)->values()->toArray();

        while ($parent) {
            $breadcrumbs[] = $this->matchRoute($parent, true, false);
            $parent = $parent->parent;
        }

        return $this->buildBreadcrumbTree($breadcrumbs);
    }

    /**
     * 添加同级菜单
     *
     * @param MenuModel $parent
     * @param MenuModel $menu
     * @param Collection $siblingList
     */
    protected function addSiblings(MenuModel $parent, MenuModel $menu, Collection $siblingList)
    {
        if (!empty($parent->children) && $parent->children->count() > 0) {
            $parent->children->filter(fn(MenuModel $item) => $item->id != $menu->id)
                ->each(fn(MenuModel $item) => $siblingList->push($this->matchRoute($item, false, false)));
        }
    }

    /**
     * 构建面包屑树
     *
     * @param array $breadcrumbs
     * @return MenuClass
     */
    protected function buildBreadcrumbTree(array $breadcrumbs): MenuClass
    {
        $result = array_pop($breadcrumbs);
        while (count($breadcrumbs) > 0) {
            $result = $this->deepMap($result, array_pop($breadcrumbs));
        }
        return $result;
    }

    /**
     * 深度赋值
     *
     * @param MenuClass $result
     * @param MenuClass $child
     * @return MenuClass
     */
    protected function deepMap(MenuClass $result, MenuClass $child): MenuClass
    {
        if (empty($result->child)) {
            $result->child = $child;
        } else {
            $result->child = $this->deepMap($result->child, $child);
        }
        return $result;
    }

    /**
     * 转换菜单类
     *
     * @param MenuModel $menu
     * @param bool $current
     * @param bool $needChild
     * @return MenuClass|null
     */
    protected function matchRoute(MenuModel $menu, bool $current = false, bool $needChild = false): ?MenuClass
    {
        $menuClass = new MenuClass;
        $menuClass->name = $menu->name;
        $menuClass->url = $menu->url;
        $menuClass->icon = $menu->icon;
        $menuClass->sort_order = $menu->sort_order;
        $menuClass->current = $current;
        $menuClass->is_show = $menu->is_show;

        if ($needChild && $menu->children && $menu->children->count()) {
            $menuClass->children = $menu->children->map(fn(MenuModel $menu) => $this->matchRoute($menu))->toArray();
        }

        return $menuClass;
    }

    /**
     * 获取后台菜单
     *
     * @param array<string> $roles
     * @return Collection<MenuClass>|\Illuminate\Support\Collection<MenuClass>
     */
    public function backendMenu(array $roles = ['*'])
    {
        $menus = MenuModel::where(['type' => MenuModel::TYPE_BACKEND, 'is_valid' => true])
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            })->orderBy('sort_order', 'DESC')->get();

        return $this->checkRole($menus, $roles);
    }

    /**
     * 获取前台菜单
     *
     * @param array<string> $roles
     * @return Collection<MenuClass>|\Illuminate\Support\Collection<MenuClass>
     */
    public function frontendMenu(array $roles = ['*'])
    {
        $menus = MenuModel::where(['type' => MenuModel::TYPE_FRONTEND, 'is_valid' => true])
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            })->orderBy('sort_order', 'DESC')->get();

        return $this->checkRole($menus, $roles);
    }

    /**
     * 过滤权限并返回菜单
     *
     * @param Collection $menus
     * @param array<string> $roles
     * @return Collection<MenuClass>|\Illuminate\Support\Collection<MenuClass>
     */
    protected function checkRole(Collection $menus, array $roles)
    {
        return $menus->filter(function (MenuModel $menu) use ($roles) {
            if (in_array('*', $roles)) {
                return true;
            }
            return is_array($menu->url) && array_key_exists('name', $menu->url) && in_array($menu->url['name'], $roles);
        })->values()->map(function (MenuModel $menu) use ($roles) {
            $object = new MenuClass;
            $object->name = $menu->name;
            $object->url = $menu->url;
            $object->icon = $menu->icon;
            $object->is_show = $menu->is_show;
            $object->children = $this->checkRole($menu->getChildren(), $roles)->toArray();
            return $object;
        });
    }
}

<?php

namespace SimpleCMS\Framework\Observers;

use SimpleCMS\Framework\Models\Menu;

class MenuObserver
{
    /**
     * Handle the Menu "saved" event.
     */
    public function saved(Menu $menu): void
    {
        if ($menu->getOriginal('type') != $menu->type) {
            $menu->children->each(fn(Menu $menu) => $menu->update(['type' => $menu->type]));
        }
    }


    /**
     * Handle the Menu "updated" event.
     */
    public function updated(Menu $menu): void
    {
        if ($menu->getOriginal('type') != $menu->type) {
            $menu->children->each(fn(Menu $menu) => $menu->update(['type' => $menu->type]));
        }
    }

    /**
     * Handle the Menu "deleted" event.
     */
    public function deleted(Menu $menu): void
    {

        $menu->children->each(fn(Menu $menu) => $menu->delete());
    }

}

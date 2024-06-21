<?php

namespace SimpleCMS\Framework\Observers;

use SimpleCMS\Framework\Models\Dict;

class DictObserver
{


    /**
     * Handle the Dict "deleted" event.
     */
    public function deleted(Dict $dict): void
    {

        $dict->items->each(fn(Dict $dict) => $dict->delete());
    }

}

<?php

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        cache()->forget('categories');
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        cache()->forget('categories');
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        cache()->forget('categories');
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        cache()->forget('categories');
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        cache()->forget('categories');
    }
}

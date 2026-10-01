<?php

namespace App\Observers;

use App\Models\BookIssue;

class BookIssueObserver
{
    /**
     * Handle the BookIssue "created" event.
     */
    public function created(BookIssue $bookIssue): void
    {
        //
    }

    /**
     * Handle the BookIssue "updated" event.
     */
    public function updated(BookIssue $bookIssue): void
    {
        //
    }

    /**
     * Handle the BookIssue "deleted" event.
     */
    public function deleted(BookIssue $bookIssue): void
    {
        //
    }

    /**
     * Handle the BookIssue "restored" event.
     */
    public function restored(BookIssue $bookIssue): void
    {
        //
    }

    /**
     * Handle the BookIssue "force deleted" event.
     */
    public function forceDeleted(BookIssue $bookIssue): void
    {
        //
    }
}

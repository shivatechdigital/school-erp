<?php

namespace App\Observers;

use App\Models\BookIssue;

class BookIssueObserver
{
    public function creating(BookIssue $issue): void
    {
        if (empty($issue->issued_by) && auth()->check()) {
            $issue->issued_by = auth()->id();
        }
    }

    public function created(BookIssue $issue): void
    {
        $book = $issue->book;

        if ($book && $book->available_copies > 0) {
            $book->decrement('available_copies');
        }
    }

    public function updated(BookIssue $issue): void
    {
        if ($issue->wasChanged('status') && $issue->status === 'returned' && $issue->getOriginal('status') === 'issued') {
            $issue->book?->increment('available_copies');
        }
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\BookIssue;
use Filament\Pages\Page;

class LibraryStatus extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Library';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Library Status';

    protected static ?string $title = 'Library Status';

    protected string $view = 'filament.pages.library-status';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    protected function getViewData(): array
    {
        $totalBooks = (int) Book::query()->sum('total_copies');
        $availableBooks = (int) Book::query()->sum('available_copies');
        $issuedCount = BookIssue::query()->where('status', 'issued')->count();

        $defaulters = BookIssue::query()
            ->with(['book', 'student', 'staff'])
            ->where('status', 'issued')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get();

        return [
            'totalBooks' => $totalBooks,
            'availableBooks' => $availableBooks,
            'issuedCount' => $issuedCount,
            'defaulters' => $defaulters,
        ];
    }
}

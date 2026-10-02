<?php

namespace App\Filament\Resources\BookIssues;

use App\Filament\Resources\BookIssues\Pages\CreateBookIssue;
use App\Filament\Resources\BookIssues\Pages\EditBookIssue;
use App\Filament\Resources\BookIssues\Pages\ListBookIssues;
use App\Filament\Resources\BookIssues\Schemas\BookIssueForm;
use App\Filament\Resources\BookIssues\Tables\BookIssuesTable;
use App\Models\BookIssue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BookIssueResource extends Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = BookIssue::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Library';

    protected static ?string $navigationLabel = 'Issue & Return Tracker';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BookIssueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookIssuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookIssues::route('/'),
            'create' => CreateBookIssue::route('/create'),
            'edit' => EditBookIssue::route('/{record}/edit'),
        ];
    }
}

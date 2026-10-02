<?php

namespace App\Filament\Resources\Homework;

use App\Filament\Resources\Homework\Pages\CreateHomework;
use App\Filament\Resources\Homework\Pages\EditHomework;
use App\Filament\Resources\Homework\Pages\ListHomework;
use App\Filament\Resources\Homework\RelationManagers\SubmissionsRelationManager;
use App\Filament\Resources\Homework\Schemas\HomeworkForm;
use App\Filament\Resources\Homework\Tables\HomeworkTable;
use App\Models\Homework;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HomeworkResource extends Resource
{
    protected static ?string $model = Homework::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|UnitEnum|null $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return HomeworkForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->when($user?->user_type === 'teacher', fn (Builder $query) => $query->where('teacher_id', $user->id));
    }

    public static function table(Table $table): Table
    {
        return HomeworkTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomework::route('/'),
            'create' => CreateHomework::route('/create'),
            'edit' => EditHomework::route('/{record}/edit'),
        ];
    }
}

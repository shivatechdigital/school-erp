<?php

namespace App\Filament\Resources\Marks;

use App\Filament\Resources\Marks\Pages\CreateMark;
use App\Filament\Resources\Marks\Pages\EditMark;
use App\Filament\Resources\Marks\Pages\ListMarks;
use App\Filament\Resources\Marks\Schemas\MarkForm;
use App\Filament\Resources\Marks\Tables\MarksTable;
use App\Models\Mark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class MarkResource extends Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = Mark::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|UnitEnum|null $navigationGroup = 'Examination';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Marks Entry';

    public static function form(Schema $schema): Schema
    {
        return MarkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarksTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->when($user?->user_type === 'teacher', fn (Builder $query) => $query->whereExists(
                DB::table('class_subject')
                    ->selectRaw('1')
                    ->whereColumn('class_subject.class_id', 'marks.class_id')
                    ->whereColumn('class_subject.subject_id', 'marks.subject_id')
                    ->where('class_subject.teacher_id', $user->id)
            ));
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarks::route('/'),
            'create' => CreateMark::route('/create'),
            'edit' => EditMark::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SectionResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Section::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->components([
            FormSection::make('Section Details')->columns(2)->schema([
                Select::make('school_id')->relationship('school', 'name')->searchable()->preload()
                    ->visible($isSuperAdmin)->required($isSuperAdmin)->live(),
                Select::make('branch_id')->relationship(
                    'branch', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                        ->when($get('school_id'), fn (Builder $branches, $schoolId) => $branches->where('school_id', $schoolId)),
                )->searchable()->preload()->required()->visible(fn (): bool => ! auth()->user()?->branch_id),
                Select::make('class_id')->relationship(
                    'class', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                        ->when($get('school_id'), fn (Builder $classes, $schoolId) => $classes->where('school_id', $schoolId)),
                )->searchable()->preload()->required(),
                Select::make('academic_year_id')->relationship(
                    'academicYear', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                        ->when($get('school_id'), fn (Builder $years, $schoolId) => $years->where('school_id', $schoolId)),
                )->searchable()->preload()->required(),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('capacity')->numeric()->minValue(1)->default(40)->required(),
                TextInput::make('current_strength')->numeric()->minValue(0)->default(0)->required(),
                TextInput::make('room_no')->maxLength(255),
                Select::make('class_teacher_id')->relationship(
                    'classTeacher',
                    'name',
                    modifyQueryUsing: fn (Builder $query, Get $get) => $query
                        ->when($get('school_id') ?? auth()->user()?->school_id, fn (Builder $users, $schoolId) => $users->where('school_id', $schoolId))
                        ->when(auth()->user()?->branch_id, fn (Builder $users, $branchId) => $users->where('branch_id', $branchId)),
                )->searchable()->preload(),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('school.name')->label('School')->searchable(),
            TextColumn::make('branch.name')->label('Branch'),
            TextColumn::make('class.name')->label('Class')->sortable()->weight('bold'),
            TextColumn::make('name')->label('Section')->sortable()->badge(),
            TextColumn::make('academicYear.name')->label('Year'),
            TextColumn::make('classTeacher.name')->label('Class Teacher'),
            TextColumn::make('current_strength')->label('Strength'),
            TextColumn::make('capacity'),
            IconColumn::make('is_active')->boolean(),
        ])->filters([
            SelectFilter::make('class_id')->relationship('class', 'name')->label('Class'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
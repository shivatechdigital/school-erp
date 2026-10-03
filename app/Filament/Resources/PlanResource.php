<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PlanResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Plan Details')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                Textarea::make('description')->rows(3)->columnSpanFull(),
                TextInput::make('monthly_price')->numeric()->minValue(0)->required(),
                TextInput::make('yearly_price')->numeric()->minValue(0)->required(),
                TextInput::make('max_students')->numeric()->minValue(0)->required(),
                TextInput::make('max_branches')->numeric()->minValue(1)->required(),
                TextInput::make('max_staff')->numeric()->minValue(0)->required(),
                TextInput::make('storage_mb')->numeric()->minValue(0)->required(),
                TagsInput::make('features')->separator(','),
                Toggle::make('is_popular'),
                Toggle::make('is_active')->default(true),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            TextColumn::make('slug')->badge(),
            TextColumn::make('monthly_price')->money('INR')->sortable(),
            TextColumn::make('yearly_price')->money('INR')->sortable(),
            TextColumn::make('max_students')->sortable(),
            TextColumn::make('max_branches')->sortable(),
            IconColumn::make('is_popular')->boolean(),
            IconColumn::make('is_active')->boolean(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])->toolbarActions([
            DeleteBulkAction::make(),
        ])->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->user_type === 'super_admin';
    }
}
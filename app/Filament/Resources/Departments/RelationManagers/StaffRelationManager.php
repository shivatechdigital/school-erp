<?php

namespace App\Filament\Resources\Departments\RelationManagers;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';

    protected static ?string $title = 'Assigned Staff';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('user_type')->label('Role')->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('designation')->placeholder('—'),
                TextColumn::make('email')->searchable(),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query
                        ->whereIn('user_type', StaffResource::STAFF_TYPES)
                        ->whereNull('department_id')),
            ])
            ->recordActions([
                DissociateAction::make(),
            ]);
    }
}

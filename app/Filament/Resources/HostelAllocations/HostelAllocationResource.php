<?php

namespace App\Filament\Resources\HostelAllocations;

use App\Filament\Resources\HostelAllocations\Pages\CreateHostelAllocation;
use App\Filament\Resources\HostelAllocations\Pages\EditHostelAllocation;
use App\Filament\Resources\HostelAllocations\Pages\ListHostelAllocations;
use App\Filament\Resources\HostelAllocations\Schemas\HostelAllocationForm;
use App\Filament\Resources\HostelAllocations\Tables\HostelAllocationsTable;
use App\Models\HostelAllocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HostelAllocationResource extends Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = HostelAllocation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|UnitEnum|null $navigationGroup = 'Hostel';

    protected static ?string $navigationLabel = 'Bed Allocations';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return HostelAllocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HostelAllocationsTable::configure($table);
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
            'index' => ListHostelAllocations::route('/'),
            'create' => CreateHostelAllocation::route('/create'),
            'edit' => EditHostelAllocation::route('/{record}/edit'),
        ];
    }
}

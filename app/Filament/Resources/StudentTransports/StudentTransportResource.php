<?php

namespace App\Filament\Resources\StudentTransports;

use App\Filament\Resources\StudentTransports\Pages\CreateStudentTransport;
use App\Filament\Resources\StudentTransports\Pages\EditStudentTransport;
use App\Filament\Resources\StudentTransports\Pages\ListStudentTransports;
use App\Filament\Resources\StudentTransports\Schemas\StudentTransportForm;
use App\Filament\Resources\StudentTransports\Tables\StudentTransportsTable;
use App\Models\StudentTransport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentTransportResource extends Resource
{
    protected static ?string $model = StudentTransport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StudentTransportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentTransportsTable::configure($table);
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
            'index' => ListStudentTransports::route('/'),
            'create' => CreateStudentTransport::route('/create'),
            'edit' => EditStudentTransport::route('/{record}/edit'),
        ];
    }
}

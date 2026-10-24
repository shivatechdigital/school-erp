<?php

namespace App\Filament\Resources\AcademicDepartments;

use App\Filament\Resources\AcademicDepartments\Pages\CreateAcademicDepartment;
use App\Filament\Resources\AcademicDepartments\Pages\EditAcademicDepartment;
use App\Filament\Resources\AcademicDepartments\Pages\ListAcademicDepartments;
use App\Filament\Resources\AcademicDepartments\Schemas\AcademicDepartmentForm;
use App\Filament\Resources\AcademicDepartments\Tables\AcademicDepartmentsTable;
use App\Filament\Resources\Concerns\HidesResourcesFromTeachers;
use App\Models\AcademicDepartment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AcademicDepartmentResource extends Resource
{
    use HidesResourcesFromTeachers;

    protected static ?string $model = AcademicDepartment::class;

    protected static string|UnitEnum|null $navigationGroup = 'Academics';

    protected static ?string $navigationLabel = 'Academic Department';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return AcademicDepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicDepartmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicDepartments::route('/'),
            'create' => CreateAcademicDepartment::route('/create'),
            'edit' => EditAcademicDepartment::route('/{record}/edit'),
        ];
    }
}

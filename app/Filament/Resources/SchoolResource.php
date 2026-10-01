<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SchoolResource extends \Filament\Resources\Resource
{
    protected static ?string $model = School::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('phone')->tel()->required()->maxLength(255),
                TextInput::make('alternate_phone')->tel()->maxLength(255),
                TextInput::make('website')->url()->maxLength(255),
                FileUpload::make('logo')->image()->directory('school-logos')->maxSize(2048),
            ]),
            Section::make('Address')->columns(3)->schema([
                Textarea::make('address')->required()->rows(2)->columnSpanFull(),
                TextInput::make('city')->required()->maxLength(255),
                TextInput::make('state')->required()->maxLength(255),
                TextInput::make('pincode')->required()->minLength(6)->maxLength(6),
                TextInput::make('country')->required()->default('India')->maxLength(255),
            ]),
            Section::make('School Details')->columns(3)->schema([
                Select::make('board')->options(array_combine(
                    ['CBSE', 'ICSE', 'State', 'IB', 'IGCSE', 'Other'],
                    ['CBSE', 'ICSE', 'State Board', 'IB', 'IGCSE', 'Other'],
                ))->required(),
                TextInput::make('affiliation_no')->maxLength(255),
                TextInput::make('recognition_no')->maxLength(255),
                Select::make('type')->options(['Boys' => 'Boys', 'Girls' => 'Girls', 'Co-Ed' => 'Co-Ed'])->required(),
                Select::make('medium')->options([
                    'English' => 'English', 'Hindi' => 'Hindi', 'Regional' => 'Regional', 'Bilingual' => 'Bilingual',
                ])->required(),
                TextInput::make('established_year')->numeric()->minValue(1800)->maxValue((int) now()->format('Y')),
            ]),
            Section::make('Platform Settings')->columns(3)->schema([
                Select::make('status')->options([
                    'pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired',
                ])->required(),
                TextInput::make('domain')->maxLength(255),
                TextInput::make('subdomain')->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('student_limit')->numeric()->required(),
                TextInput::make('storage_limit_mb')->numeric()->required()->label('Storage Limit (MB)'),
                ColorPicker::make('primary_color')->required(),
                ColorPicker::make('secondary_color')->required(),
                DateTimePicker::make('activated_at'),
                DateTimePicker::make('suspended_at'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->circular(),
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('code')->searchable()->badge(),
                TextColumn::make('board')->badge(),
                TextColumn::make('city')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success', 'pending' => 'warning', 'suspended' => 'danger', default => 'gray',
                }),
                TextColumn::make('students_count')->counts('students')->label('Students')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active' => 'Active', 'pending' => 'Pending', 'suspended' => 'Suspended', 'expired' => 'Expired',
                ]),
                SelectFilter::make('board')->options([
                    'CBSE' => 'CBSE', 'ICSE' => 'ICSE', 'State' => 'State', 'IB' => 'IB', 'IGCSE' => 'IGCSE',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'view' => Pages\ViewSchool::route('/{record}'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->user_type === 'super_admin';
    }
}
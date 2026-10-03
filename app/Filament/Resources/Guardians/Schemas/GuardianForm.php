<?php

namespace App\Filament\Resources\Guardians\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class GuardianForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Guardian Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        FileUpload::make('photo')
                            ->image()
                            ->directory('guardian-photos')
                            ->avatar()
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Full name'),

                        Select::make('relation')
                            ->options([
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Guardian',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('father'),

                        Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ]),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->maxDate(now()->subYears(18))
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Toggle::make('is_primary_contact')
                            ->label('Primary Contact?')
                            ->helperText('Emergency mein sabse pehle call hoga'),

                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(3),

                Section::make('Contact Details')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(15)
                            ->helperText('Primary mobile number'),

                        TextInput::make('alternate_phone')
                            ->tel()
                            ->maxLength(15)
                            ->label('Alternate Phone'),

                        TextInput::make('whatsapp')
                            ->tel()
                            ->maxLength(15)
                            ->helperText('WhatsApp alerts ke liye'),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Occupation Details')
                    ->icon('heroicon-o-briefcase')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('occupation')
                            ->placeholder('e.g., Business, Doctor, Engineer'),

                        TextInput::make('company_name')
                            ->label('Company / Office Name'),

                        TextInput::make('annual_income')
                            ->numeric()
                            ->prefix('₹')
                            ->label('Annual Income'),
                    ])->columns(3),

                Section::make('Address')
                    ->icon('heroicon-o-map-pin')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),

                        TextInput::make('city'),
                        TextInput::make('state'),
                        TextInput::make('pincode')
                            ->maxLength(6)
                            ->numeric(),
                    ])->columns(3),

                Section::make('Documents')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('aadhaar_no')
                            ->label('Aadhaar Number')
                            ->maxLength(12)
                            ->numeric(),

                        TextInput::make('pan_no')
                            ->label('PAN Number')
                            ->maxLength(10),
                    ])->columns(2),

                Section::make('Linked Students')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Select::make('students')
                            ->relationship(
                                name: 'students',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('students.status', 'active'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Student $record): string => "{$record->full_name} — {$record->admission_no} ({$record->class?->name})")
                            ->multiple()
                            ->preload()
                            ->searchable(['first_name', 'last_name', 'admission_no'])
                            ->helperText('Is guardian ke bachche select karo'),
                    ])->columns(1),
            ]);
    }
}

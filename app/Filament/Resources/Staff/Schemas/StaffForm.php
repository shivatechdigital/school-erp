<?php

namespace App\Filament\Resources\Staff\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->components([
                Section::make('Personal Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        FileUpload::make('profile_photo')
                            ->image()
                            ->directory('staff-photos')
                            ->avatar()
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(15),

                        TextInput::make('alternate_phone')
                            ->tel()
                            ->maxLength(15),

                        Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->maxDate(now()->subYears(18))
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Select::make('blood_group')
                            ->options([
                                'A+' => 'A+', 'A-' => 'A-',
                                'B+' => 'B+', 'B-' => 'B-',
                                'AB+' => 'AB+', 'AB-' => 'AB-',
                                'O+' => 'O+', 'O-' => 'O-',
                            ]),
                    ])->columns(3),

                Section::make('Employment Details')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('employee_code')
                            ->label('Employee ID')
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'EMP-'.date('Y').'-'.str_pad((string) (User::count() + 1), 4, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated(),

                        Select::make('user_type')
                            ->label('Role')
                            ->options(fn (): array => array_filter([
                                'school_admin' => in_array(auth()->user()?->user_type, ['super_admin', 'school_admin'], true) ? 'School Admin' : null,
                                'branch_admin' => 'Branch Admin',
                                'teacher' => 'Teacher',
                                'accountant' => 'Accountant',
                                'librarian' => 'Librarian',
                                'transport_manager' => 'Transport Manager',
                                'receptionist' => 'Receptionist',
                                'hr' => 'HR',
                                'doctor' => 'Doctor',
                            ]))
                            ->required()
                            ->default('teacher'),

                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('departmentRecord', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('designation')
                            ->label('Designation')
                            ->datalist([
                                'Principal', 'Vice Principal', 'HOD',
                                'TGT', 'PGT', 'PRT', 'NTT',
                                'Clerk', 'Accountant', 'Librarian',
                            ]),

                        TextInput::make('qualification')
                            ->placeholder('e.g., M.Sc, B.Ed, PhD'),

                        DatePicker::make('joining_date')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        TextInput::make('salary')
                            ->numeric()
                            ->prefix('₹')
                            ->placeholder('e.g., 35000'),

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active'),
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

                Section::make('Login Credentials')
                    ->icon('heroicon-o-key')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        // The User model's "hashed" cast hashes this on save.
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Edit mein blank chhodo toh password same rahega'),
                    ])->columns(1),
            ]);
    }
}

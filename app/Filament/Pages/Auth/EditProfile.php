<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile Photo')
                ->icon('heroicon-o-camera')
                ->schema([
                    FileUpload::make('profile_photo')
                        ->label('')
                        ->image()
                        ->avatar()
                        ->directory('staff-photos')
                        ->maxSize(2048),
                ]),

            Section::make('Account Details')
                ->icon('heroicon-o-identification')
                ->description('Managed by your school administrator. Contact them to request a change.')
                ->schema([
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->label('Email')->disabled(),
                    TextInput::make('employee_code')->label('Employee ID')->disabled(),
                    Select::make('user_type')
                        ->label('Role')
                        ->options([
                            'super_admin' => 'Super Admin',
                            'school_admin' => 'School Admin',
                            'branch_admin' => 'Branch Admin',
                            'teacher' => 'Teacher',
                            'accountant' => 'Accountant',
                            'librarian' => 'Librarian',
                            'transport_manager' => 'Transport Manager',
                            'receptionist' => 'Receptionist',
                            'parent' => 'Parent',
                            'student' => 'Student',
                            'driver' => 'Driver',
                        ])
                        ->disabled(),
                ])->columns(2),

            Section::make('Personal Details')
                ->icon('heroicon-o-user')
                ->description('Managed by your school administrator. Contact them to request a change.')
                ->schema([
                    Select::make('gender')
                        ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
                        ->disabled(),
                    DatePicker::make('date_of_birth')->label('Date of Birth')->native(false)->displayFormat('d/m/Y')->disabled(),
                    TextInput::make('qualification')->disabled(),
                    TextInput::make('designation')->disabled(),
                    TextInput::make('department')->disabled(),
                    DatePicker::make('joining_date')->label('Joining Date')->native(false)->displayFormat('d/m/Y')->disabled(),
                ])->columns(3),

            Section::make('Contact Details')
                ->icon('heroicon-o-phone')
                ->schema([
                    TextInput::make('phone')->tel()->maxLength(15),
                    TextInput::make('alternate_phone')->tel()->maxLength(15),
                    Select::make('blood_group')
                        ->options([
                            'A+' => 'A+', 'A-' => 'A-',
                            'B+' => 'B+', 'B-' => 'B-',
                            'AB+' => 'AB+', 'AB-' => 'AB-',
                            'O+' => 'O+', 'O-' => 'O-',
                        ]),
                    Textarea::make('address')->rows(2)->columnSpanFull(),
                    TextInput::make('city'),
                    TextInput::make('state'),
                    TextInput::make('pincode')->numeric()->maxLength(6),
                ])->columns(3),

            Section::make('Change Password')
                ->icon('heroicon-o-key')
                ->collapsible()
                ->collapsed()
                ->schema([
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                    $this->getCurrentPasswordFormComponent(),
                ]),
        ]);
    }
}

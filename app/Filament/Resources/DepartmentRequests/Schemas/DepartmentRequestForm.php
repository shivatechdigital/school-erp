<?php

namespace App\Filament\Resources\DepartmentRequests\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DepartmentRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Request Details')->columns(2)->schema([
                    Select::make('department_id')
                        ->label('Department')
                        ->relationship(
                            'department',
                            'name',
                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                ->where('school_id', auth()->user()?->school_id),
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn (): ?int => auth()->user()?->department_id)
                        ->disabled(fn (): bool => ! self::isAdmin() && (bool) auth()->user()?->department_id)
                        ->dehydrated(),
                    Select::make('type')
                        ->options([
                            'issue' => 'Issue / Problem',
                            'report' => 'General Report',
                            'item_request' => 'Item Request',
                        ])
                        ->required(),
                    TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Textarea::make('description')->required()->rows(4)->maxLength(5000)->columnSpanFull(),
                    FileUpload::make('attachment')
                        ->label('Attachment (optional)')
                        ->disk('local')
                        ->directory('department-requests')
                        ->acceptedFileTypes([
                            'image/jpeg', 'image/png', 'application/pdf', 'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ]),
            ]);
    }

    private static function isAdmin(): bool
    {
        return in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true);
    }
}

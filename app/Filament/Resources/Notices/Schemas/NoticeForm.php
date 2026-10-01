<?php

namespace App\Filament\Resources\Notices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class NoticeForm
{
    public const AUDIENCES = [
        'all' => 'Entire School (Staff, Students & Parents)',
        'staff' => 'Staff Only',
        'students' => 'All Students & Parents',
        'guardians' => 'Parents/Guardians Only',
        'specific_class' => 'Specific Class / Section',
    ];

    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';
        $isSpecificClass = fn (Get $get): bool => $get('target_audience') === 'specific_class';

        return $schema
            ->components([
                Section::make('Notice Details')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('title')
                            ->label('Title / Headline')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('priority')
                            ->label('Priority')
                            ->options([
                                'normal' => 'Normal',
                                'high' => 'High',
                                'urgent' => 'Urgent / Alert 🚨',
                            ])
                            ->default('normal')
                            ->required(),

                        RichEditor::make('content')
                            ->label('Notice Content')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3',
                            ])
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Audience & Timing')
                    ->schema([
                        Select::make('target_audience')
                            ->label('Target Audience')
                            ->options(self::AUDIENCES)
                            ->default('all')
                            ->live()
                            ->required(),

                        Select::make('class_id')
                            ->label('Class')
                            ->relationship('schoolClass', 'name')
                            ->visible($isSpecificClass)
                            ->required($isSpecificClass)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),

                        Select::make('section_id')
                            ->label('Section (Optional)')
                            ->relationship('section', 'name', fn (Builder $query, Get $get) => $query->where('class_id', $get('class_id')))
                            ->visible($isSpecificClass),

                        DatePicker::make('publish_date')
                            ->label('Publish Date')
                            ->default(now())
                            ->required(),

                        DatePicker::make('expiry_date')
                            ->label('Expiry Date (Optional)')
                            ->afterOrEqual('publish_date'),

                        FileUpload::make('attachments')
                            ->label('Circular / Attachments (PDF/Images)')
                            ->multiple()
                            ->disk('public')
                            ->directory('notices')
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),

                        Toggle::make('is_published')
                            ->label('Publish immediately')
                            ->default(true),

                        Toggle::make('send_notification')
                            ->label('Send Push / SMS Alert to Audience')
                            ->default(false),
                    ])->columns(3),
            ]);
    }
}

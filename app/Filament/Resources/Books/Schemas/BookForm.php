<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Book Information')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('title')
                            ->label('Book Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('book_category_id')
                            ->label('Category / Genre')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description'),
                            ])
                            ->required(),

                        TextInput::make('isbn_no')
                            ->label('ISBN / Barcode No.')
                            ->placeholder('e.g. 978-0-123456-47-2')
                            ->maxLength(50),

                        TextInput::make('author')
                            ->label('Author')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('publisher')
                            ->label('Publisher')
                            ->maxLength(255),

                        TextInput::make('edition')
                            ->label('Edition / Year')
                            ->placeholder('e.g. 4th Edition, 2024')
                            ->maxLength(50),

                        TextInput::make('rack_no')
                            ->label('Rack / Shelf Location')
                            ->placeholder('e.g. Rack A-3, Shelf 2')
                            ->maxLength(50),

                        TextInput::make('total_copies')
                            ->label('Total Copies')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('available_copies', $state))
                            ->required(),

                        TextInput::make('available_copies')
                            ->label('Available Copies')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (Get $get): ?int => filled($get('total_copies')) ? (int) $get('total_copies') : null)
                            ->default(1)
                            ->required(),

                        TextInput::make('price')
                            ->label('Cost / Price (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->default(0.00),

                        FileUpload::make('cover_image')
                            ->label('Book Cover Image')
                            ->image()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('library/books')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Books\Tables;

use App\Models\Book;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl('/images/book-placeholder.png'),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Book $record): string => "By {$record->author}"),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info'),

                TextColumn::make('isbn_no')
                    ->label('ISBN / Barcode')
                    ->default('-')
                    ->searchable(),

                TextColumn::make('rack_no')
                    ->label('Location')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('available_copies')
                    ->label('Stock (Avail/Total)')
                    ->formatStateUsing(fn (Book $record): string => "{$record->available_copies} / {$record->total_copies}")
                    ->badge()
                    ->color(fn (Book $record): string => $record->available_copies > 0 ? 'success' : 'danger')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('book_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

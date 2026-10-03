<?php

namespace App\Filament\Resources\FeeCollections\Schemas;

use App\Models\FeeCollection;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FeeCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Fee Payment')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $r): string => "{$r->full_name} ({$r->admission_no})")
                            ->searchable(['first_name', 'last_name', 'admission_no'])
                            ->preload()
                            ->required(),

                        TextInput::make('receipt_no')
                            ->default(fn (): string => FeeCollection::generateReceiptNo())
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('amount')
                            ->label('Fee Amount')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->required()
                            ->live(),

                        TextInput::make('fine_amount')
                            ->label('Late Fine')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->default(0)
                            ->live(),

                        TextEntry::make('total_display')
                            ->label('Total')
                            ->state(fn (Get $get): string => '₹'.number_format(
                                (float) ($get('amount') ?? 0) + (float) ($get('fine_amount') ?? 0), 2
                            )),

                        Select::make('payment_mode')
                            ->options([
                                'cash' => '💵 Cash',
                                'upi' => '📱 UPI',
                                'card' => '💳 Card',
                                'net_banking' => '🏦 Net Banking',
                                'cheque' => '📄 Cheque',
                                'dd' => '📄 Demand Draft',
                                'online' => '🌐 Online',
                            ])
                            ->required()
                            ->default('cash')
                            ->live(),

                        TextInput::make('transaction_id')
                            ->label('Transaction / UPI Ref')
                            ->visible(fn (Get $get): bool => in_array($get('payment_mode'), ['upi', 'card', 'net_banking', 'online'], true)),

                        TextInput::make('cheque_no')
                            ->visible(fn (Get $get): bool => $get('payment_mode') === 'cheque'),

                        TextInput::make('bank_name')
                            ->visible(fn (Get $get): bool => in_array($get('payment_mode'), ['cheque', 'dd'], true)),

                        DatePicker::make('payment_date')
                            ->required()
                            ->default(now())
                            ->maxDate(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Textarea::make('remark')->rows(2),
                    ])->columns(2),
            ]);
    }
}

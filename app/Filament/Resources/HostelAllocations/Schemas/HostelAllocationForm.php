<?php

namespace App\Filament\Resources\HostelAllocations\Schemas;

use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class HostelAllocationForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->components([
                Section::make('Allocation Details')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $record): string => "{$record->full_name} (Adm: {$record->admission_no})")
                            ->searchable(['first_name', 'last_name', 'admission_no'])
                            ->preload()
                            ->required(),

                        Select::make('hostel_room_id')
                            ->label('Hostel Room')
                            ->relationship('room', 'room_number', fn (Builder $query) => $query->where('is_active', true)->with(['hostel', 'activeAllocations']))
                            ->getOptionLabelFromRecordUsing(fn (HostelRoom $record): string => "{$record->hostel?->name} - Room {$record->room_number} ({$record->available_beds} Vacant)")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->rules([
                                fn (Get $get, ?HostelAllocation $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record): void {
                                    if (! $value || $get('status') === 'vacated') {
                                        return;
                                    }

                                    // Re-saving an existing active allocation in the same room should not count against capacity.
                                    if ($record?->status === 'allocated' && (int) $record->hostel_room_id === (int) $value) {
                                        return;
                                    }

                                    $room = HostelRoom::find($value);

                                    if ($room && $room->available_beds <= 0) {
                                        $fail('This room has no vacant beds available.');
                                    }
                                },
                            ]),

                        TextInput::make('bed_number')
                            ->label('Bed Identifier / Name')
                            ->placeholder('e.g. Bed 1, Lower Berth')
                            ->maxLength(20),

                        DatePicker::make('allocated_date')
                            ->label('Allocated Date')
                            ->default(now())
                            ->required(),

                        Select::make('status')
                            ->options([
                                'allocated' => 'Currently Staying (Allocated)',
                                'vacated' => 'Vacated',
                            ])
                            ->default('allocated')
                            ->live()
                            ->required(),

                        DatePicker::make('vacated_date')
                            ->label('Vacated Date')
                            ->afterOrEqual('allocated_date')
                            ->visible(fn (Get $get): bool => $get('status') === 'vacated')
                            ->required(fn (Get $get): bool => $get('status') === 'vacated'),

                        Textarea::make('remarks')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}

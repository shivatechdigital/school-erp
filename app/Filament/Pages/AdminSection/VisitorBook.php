<?php

namespace App\Filament\Pages\AdminSection;

use App\Models\VisitorBookEntry as VisitorBookRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;

class VisitorBook extends AdminSectionManagementPage
{
    protected static string $model = VisitorBookRecord::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Visitor Book';

    protected static ?string $title = 'Visitor Book';

    protected function recordLabel(): string
    {
        return 'Visitor Entry';
    }

    protected function defaultFormData(): array
    {
        return [
            'number_of_people' => 1,
            'people' => [['name' => '', 'id_number' => '']],
            'visit_date' => now()->toDateString(),
            'in_time' => now()->format('H:i'),
        ];
    }

    protected function recordFormComponents(): array
    {
        return [
            TextInput::make('purpose')->required()->maxLength(255),
            TextInput::make('number_of_people')
                ->label('Number of People')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                    $count = max(1, min(20, (int) $state));
                    $people = array_slice(array_values($get('people') ?? []), 0, $count);

                    while (count($people) < $count) {
                        $people[] = ['name' => '', 'id_number' => ''];
                    }

                    $set('people', $people);
                }),
            Repeater::make('people')
                ->label('Visitor Details')
                ->schema([
                    TextInput::make('name')->label('Name')->required()->maxLength(255),
                    TextInput::make('id_number')->label('ID Number')->required()->maxLength(100),
                ])
                ->columns(2)
                ->minItems(fn (Get $get): int => max(1, (int) ($get('number_of_people') ?? 1)))
                ->maxItems(fn (Get $get): int => max(1, (int) ($get('number_of_people') ?? 1)))
                ->addable(false)
                ->deletable(false)
                ->reorderable(false),
            TextInput::make('phone')->label('Contact Phone Number')->tel()->required()->maxLength(20),
            DatePicker::make('visit_date')->label('Visit Date')->required()->native(false),
            TimePicker::make('in_time')->label('In Time')->required(),
            TimePicker::make('out_time')->label('Out Time'),
            FileUpload::make('documents')
                ->label('Documents')
                ->multiple()
                ->maxFiles(10)
                ->disk('local')
                ->directory('admin/visitor-book')
                ->acceptedFileTypes([
                    'image/jpeg', 'image/png', 'application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ])
                ->maxSize(10240),
        ];
    }

    protected function recordTableColumns(): array
    {
        return [
            TextColumn::make('purpose')->searchable()->wrap(),
            TextColumn::make('people')
                ->label('Visitors')
                ->state(fn (VisitorBookRecord $record): string => collect($record->people)->pluck('name')->implode(', '))
                ->wrap(),
            TextColumn::make('phone')->label('Phone'),
            TextColumn::make('visit_date')->date()->sortable(),
            TextColumn::make('in_time')->label('In'),
            TextColumn::make('out_time')->label('Out')->placeholder('—'),
        ];
    }

    protected function recordFileFields(): array
    {
        return ['documents'];
    }
}

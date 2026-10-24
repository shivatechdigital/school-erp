<?php

namespace App\Filament\Pages\AdminSection;

use App\Models\StudentIdCard;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentIdCardManagement extends AdminSectionManagementPage
{
    protected static string $model = StudentIdCard::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'ID Card';

    protected static ?string $title = 'ID Card';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createIdCard')
                ->label('Create ID Card')
                ->icon('heroicon-o-plus')
                ->action(function (): void {
                    $this->resetForm();
                }),
        ];
    }

    protected function recordLabel(): string
    {
        return 'ID Card';
    }

    protected function recordFormComponents(): array
    {
        return [
            TextInput::make('student_name')->label('Student Name')->required()->maxLength(255),
            FileUpload::make('photo')
                ->label('Student Photo')
                ->image()
                ->acceptedFileTypes(['image/jpeg'])
                ->disk('public')
                ->directory('admin/id-cards')
                ->maxSize(2048)
                ->required(),
            Select::make('class_id')
                ->label('Class')
                ->relationship(
                    'class',
                    'name',
                    modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                        ->when(
                            $get('school_id') ?? auth()->user()?->school_id,
                            fn (Builder $classes, int|string $schoolId): Builder => $classes->where('school_id', $schoolId),
                        ),
                )
                ->searchable()
                ->preload()
                ->required(),
        ];
    }

    protected function getRecordsQuery(): Builder
    {
        return parent::getRecordsQuery()->with('class');
    }

    protected function recordTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('S.N.')->sortable(),
            ImageColumn::make('photo')->label('Student')->circular(),
            TextColumn::make('student_name')->label('Student Name')->searchable()->sortable(),
            TextColumn::make('class.name')->label('Class'),
        ];
    }

    protected function additionalRecordActions(): array
    {
        return [
            Action::make('downloadCard')
                ->label('Download ID Card')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (Model $record): string => route('admin-section.id-cards.download', $record))
                ->openUrlInNewTab(),
        ];
    }

    protected function recordFileFields(): array
    {
        return ['photo'];
    }

    protected function recordFileDisk(): string
    {
        return 'public';
    }
}

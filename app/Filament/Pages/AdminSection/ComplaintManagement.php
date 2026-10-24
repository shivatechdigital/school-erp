<?php

namespace App\Filament\Pages\AdminSection;

use App\Models\Complaint as SchoolComplaint;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class ComplaintManagement extends AdminSectionManagementPage
{
    protected static string $model = SchoolComplaint::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Complaint';

    protected static ?string $title = 'Complaint';

    protected function recordLabel(): string
    {
        return 'Complaint';
    }

    protected function defaultFormData(): array
    {
        return ['complaint_date' => now()->toDateString()];
    }

    protected function recordFormComponents(): array
    {
        return [
            TextInput::make('complainant_name')->label('Complainant')->required()->maxLength(255),
            TextInput::make('against_name')->label('Complaint Against')->required()->maxLength(255),
            DatePicker::make('complaint_date')->label('Complaint Date')->required()->native(false),
            Textarea::make('details')->label('Complaint Details')->required()->rows(4)->maxLength(5000),
            FileUpload::make('attachment')
                ->label('Image or Document (optional)')
                ->disk('local')
                ->directory('admin/complaints')
                ->acceptedFileTypes([
                    'image/jpeg', 'image/png', 'image/webp', 'application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ])
                ->maxSize(10240),
        ];
    }

    protected function recordTableColumns(): array
    {
        return [
            TextColumn::make('complainant_name')->label('Complainant')->searchable()->sortable(),
            TextColumn::make('against_name')->label('Against')->searchable(),
            TextColumn::make('complaint_date')->date()->sortable(),
            TextColumn::make('details')->limit(48)->wrap(),
            TextColumn::make('attachment')->label('File')->formatStateUsing(fn (?string $state): string => filled($state) ? 'Attached' : '—'),
        ];
    }

    protected function recordFileFields(): array
    {
        return ['attachment'];
    }
}

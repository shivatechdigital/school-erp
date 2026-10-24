<?php

namespace App\Filament\Pages\AdminSection;

use App\Models\Certificate as SchoolCertificate;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class CertificateManagement extends AdminSectionManagementPage
{
    protected static string $model = SchoolCertificate::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Certificate';

    protected static ?string $title = 'Certificate';

    protected function recordLabel(): string
    {
        return 'Certificate';
    }

    protected function recordFormComponents(): array
    {
        return [
            TextInput::make('name')->label('Certificate Name')->required()->maxLength(255),
            FileUpload::make('image')
                ->label('Certificate Image')
                ->image()
                ->disk('public')
                ->directory('admin/certificates')
                ->maxSize(5120)
                ->required(),
        ];
    }

    protected function recordTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('S.I.')->sortable(),
            TextColumn::make('name')->label('Certificate Name')->searchable()->sortable(),
            ImageColumn::make('image')->label('Certificate Image')->height(48),
        ];
    }

    protected function recordFileFields(): array
    {
        return ['image'];
    }

    protected function recordFileDisk(): string
    {
        return 'public';
    }
}

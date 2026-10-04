<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Services\StaffBulkImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadDemo')
                ->label('Download Demo')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (StaffBulkImportService $service) => response()->streamDownload(
                    fn () => print($service->templateCsv()),
                    'staff_bulk_upload_template.csv',
                    ['Content-Type' => 'text/csv'],
                )),

            Action::make('uploadList')
                ->label('Upload List')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Bulk Upload Staff')
                ->modalDescription('Pehle "Download Demo" se template download karo, usme sample row delete karke apna data bharo, fir yahan upload karo. Employee ID aur password apne aap assign ho jayenge (default password: password).')
                ->form([
                    FileUpload::make('import_file')
                        ->label('Excel / CSV file')
                        ->disk('local')
                        ->directory('staff-imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', '.csv'])
                        ->required(),
                ])
                ->action(function (array $data, StaffBulkImportService $service): void {
                    $path = Storage::disk('local')->path($data['import_file']);

                    $result = $service->import($path, auth()->user());

                    Storage::disk('local')->delete($data['import_file']);

                    $errorList = collect($result['errors'])->take(10)->implode("\n");
                    if (count($result['errors']) > 10) {
                        $errorList .= "\n…and ".(count($result['errors']) - 10).' more.';
                    }

                    Notification::make()
                        ->title("Import complete: {$result['created']} staff created, {$result['skipped']} skipped")
                        ->body($errorList ?: null)
                        ->status($result['skipped'] > 0 ? 'warning' : 'success')
                        ->persistent()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}

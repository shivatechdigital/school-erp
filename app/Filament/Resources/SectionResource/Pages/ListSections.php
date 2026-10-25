<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Services\SectionAutoAllotmentService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    protected static string $resource = SectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('autoAllotStudents')
                ->label('Auto-allot Students')
                ->icon('heroicon-o-squares-plus')
                ->form([
                    Select::make('class_id')
                        ->label('Class')
                        ->options(fn (): array => SchoolClass::query()
                            ->where('school_id', auth()->user()?->school_id)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    Select::make('academic_year_id')
                        ->label('Academic Year')
                        ->options(fn (): array => AcademicYear::query()
                            ->where('school_id', auth()->user()?->school_id)
                            ->orderByDesc('start_date')
                            ->pluck('name', 'id')
                            ->all())
                        ->default(fn (): ?int => AcademicYear::query()
                            ->where('school_id', auth()->user()?->school_id)
                            ->where('is_current', true)
                            ->value('id'))
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $class = SchoolClass::query()->findOrFail($data['class_id']);
                    $academicYear = AcademicYear::query()->findOrFail($data['academic_year_id']);

                    $result = app(SectionAutoAllotmentService::class)->allot($class, $academicYear);

                    if ($result['allotted'] === 0) {
                        Notification::make()->warning()->title('No unassigned students found for this class/year')->send();

                        return;
                    }

                    $lines = collect($result['sections'])
                        ->map(fn (int $count, string $name): string => "{$name}: {$count}")
                        ->implode(', ');

                    $createdNote = $result['created_sections'] !== []
                        ? ' New section(s) created: '.implode(', ', $result['created_sections']).'.'
                        : '';

                    Notification::make()
                        ->success()
                        ->title($result['allotted'].' students allotted')
                        ->body($lines.$createdNote)
                        ->send();
                }),
        ];
    }
}

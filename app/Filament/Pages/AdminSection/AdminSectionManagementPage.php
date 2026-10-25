<?php

namespace App\Filament\Pages\AdminSection;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

abstract class AdminSectionManagementPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $model;

    protected static string|\UnitEnum|null $navigationGroup = 'Admin Section';

    protected static ?int $navigationSort = null;

    protected string $view = 'filament.pages.admin-section.management';

    public ?array $data = [];

    public ?int $editingRecordId = null;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true);
    }

    public function mount(): void
    {
        $this->form->fill($this->defaultFormData());
    }

    public function form(Schema $schema): Schema
    {
        $modelClass = static::$model;

        return $schema
            ->model(new $modelClass)
            ->components([
                ...$this->recordFormComponents(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getRecordsQuery())
            ->columns($this->recordTableColumns())
            ->recordActions([
                Action::make('editRecord')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->action(function (Model $record): void {
                        $this->editRecord((int) $record->getKey());
                    }),
                DeleteAction::make()
                    ->after(function (Model $record): void {
                        $this->deleteRecordFiles($record);
                    }),
                ...$this->additionalRecordActions(),
            ])
            ->defaultSort('id', 'desc');
    }

    public function saveRecord(): void
    {
        $data = $this->form->getState();
        $modelClass = static::$model;

        if ($this->editingRecordId) {
            $record = $this->getRecordsQuery()->findOrFail($this->editingRecordId);
            $previousFiles = $this->recordFilePaths($record);
            $record->fill($data);
            $record->save();
            $this->deleteRemovedFiles($previousFiles, $this->recordFilePaths($record));
            $message = $this->recordLabel().' updated';
        } else {
            $record = new $modelClass;
            $data['school_id'] ??= auth()->user()?->school_id;
            $data['branch_id'] ??= auth()->user()?->branch_id;

            if ($record->isFillable('created_by')) {
                $data['created_by'] = auth()->id();
            }

            $record->fill($data);
            $record->save();
            $message = $this->recordLabel().' created';
        }

        $this->resetForm();
        Notification::make()->success()->title($message)->send();
    }

    public function editRecord(int $recordId): void
    {
        $record = $this->getRecordsQuery()->findOrFail($recordId);
        $this->editingRecordId = $recordId;
        $this->form->model($record)->fill($record->attributesToArray());
    }

    public function resetForm(): void
    {
        $this->editingRecordId = null;
        $this->resetValidation();
        $modelClass = static::$model;
        $this->form->model(new $modelClass)->fill($this->defaultFormData());
    }

    public function formHeading(): string
    {
        return ($this->editingRecordId ? 'Edit ' : 'Create ').$this->recordLabel();
    }

    public function submitLabel(): string
    {
        return $this->editingRecordId ? 'Save changes' : 'Create '.$this->recordLabel();
    }

    protected function getRecordsQuery(): Builder
    {
        $modelClass = static::$model;

        return $modelClass::query();
    }

    protected function defaultFormData(): array
    {
        return [];
    }

    protected function recordFileFields(): array
    {
        return [];
    }

    protected function recordFileDisk(): string
    {
        return 'local';
    }

    protected function additionalRecordActions(): array
    {
        return [];
    }

    abstract protected function recordLabel(): string;

    abstract protected function recordFormComponents(): array;

    abstract protected function recordTableColumns(): array;

    private function recordFilePaths(Model $record): array
    {
        $paths = [];

        foreach ($this->recordFileFields() as $field) {
            $value = $record->getAttribute($field);
            $paths = [...$paths, ...Arr::wrap($value)];
        }

        return array_values(array_filter($paths, 'is_string'));
    }

    private function deleteRemovedFiles(array $previousPaths, array $currentPaths): void
    {
        $removedPaths = array_diff($previousPaths, $currentPaths);

        if ($removedPaths !== []) {
            Storage::disk($this->recordFileDisk())->delete($removedPaths);
        }
    }

    private function deleteRecordFiles(Model $record): void
    {
        $paths = $this->recordFilePaths($record);

        if ($paths !== []) {
            Storage::disk($this->recordFileDisk())->delete($paths);
        }
    }
}

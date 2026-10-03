<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Exam;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class StudentResource extends \Filament\Resources\Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Students';

    protected static ?string $recordTitleAttribute = 'admission_no';

    public static function form(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->columns(1)->components([
            FormSection::make('School and Branch')->columns(2)->schema([
                Select::make('school_id')->relationship('school', 'name')->searchable()->preload()
                    ->visible($isSuperAdmin)->required($isSuperAdmin)->live(),
                Select::make('branch_id')->relationship(
                    'branch',
                    'name',
                    modifyQueryUsing: fn (Builder $query, Get $get) => $query
                        ->when($get('school_id'), fn (Builder $branches, $schoolId) => $branches->where('school_id', $schoolId)),
                )->searchable()->preload()->required()->visible(fn (): bool => ! auth()->user()?->branch_id),
            ]),
            FormSection::make('Personal Information')->columns(3)->schema([
                FileUpload::make('photo')->image()->directory('student-photos')->maxSize(2048),
                TextInput::make('first_name')->required()->maxLength(255),
                TextInput::make('middle_name')->maxLength(255),
                TextInput::make('last_name')->maxLength(255),
                Select::make('gender')->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])->required(),
                DatePicker::make('date_of_birth')->required()->native(false),
                TextInput::make('birth_place')->maxLength(255),
                Select::make('blood_group')->options(array_combine(
                    ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
                    ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
                )),
                TextInput::make('religion')->maxLength(255),
                TextInput::make('caste')->maxLength(255),
                Select::make('category')->options(array_combine(
                    ['General', 'OBC', 'SC', 'ST', 'EWS', 'Other'],
                    ['General', 'OBC', 'SC', 'ST', 'EWS', 'Other'],
                ))->required(),
                TextInput::make('nationality')->default('Indian')->maxLength(255),
                TextInput::make('mother_tongue')->maxLength(255),
                TextInput::make('aadhaar_no')->minLength(12)->maxLength(12)->numeric(),
                TextInput::make('samagra_id')->maxLength(255),
            ]),
            FormSection::make('Academic Information')->columns(3)->schema([
                TextInput::make('admission_no')->required()->maxLength(255)
                    ->default(fn (): string => 'ADM-'.now()->format('Y').'-'.str_pad((string) (Student::withoutGlobalScopes()->count() + 1), 4, '0', STR_PAD_LEFT))
                    ->unique(ignoreRecord: true),
                TextInput::make('roll_no')->maxLength(255),
                TextInput::make('gr_no')->maxLength(255),
                TextInput::make('student_code')->maxLength(255)->unique(ignoreRecord: true),
                Select::make('academic_year_id')->relationship(
                    'academicYear',
                    'name',
                    modifyQueryUsing: fn (Builder $query, Get $get) => $query
                        ->when($get('school_id'), fn (Builder $years, $schoolId) => $years->where('school_id', $schoolId)),
                )->searchable()->preload()->required(),
                Select::make('class_id')->relationship(
                    'class',
                    'name',
                    modifyQueryUsing: fn (Builder $query, Get $get) => $query
                        ->when($get('school_id'), fn (Builder $classes, $schoolId) => $classes->where('school_id', $schoolId)),
                )->searchable()->preload()->required()->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('section_id', null)),
                Select::make('section_id')->options(function (Get $get): array {
                    if (! $get('class_id')) {
                        return [];
                    }

                    return Section::query()
                        ->where('class_id', $get('class_id'))
                        ->when($get('school_id'), fn (Builder $query, $schoolId) => $query->where('school_id', $schoolId))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all();
                })->searchable()->required(),
                Select::make('house_id')
                    ->label('House')
                    ->relationship(
                        'house',
                        'name',
                        modifyQueryUsing: fn (Builder $query, Get $get) => $query
                            ->when($get('school_id'), fn (Builder $houses, $schoolId) => $houses->where('school_id', $schoolId)),
                    )->searchable()->preload(),
                DatePicker::make('admission_date')->required()->default(now())->native(false),
                Select::make('admission_type')->options([
                    'new' => 'New Admission', 'transfer' => 'Transfer', 're-admission' => 'Re-Admission',
                ])->required(),
                TextInput::make('previous_school')->maxLength(255),
                TextInput::make('previous_class')->maxLength(255),
            ]),
            FormSection::make('Contact and Address')->columns(3)->schema([
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(15),
                Textarea::make('current_address')->rows(2)->columnSpanFull(),
                Textarea::make('permanent_address')->rows(2)->columnSpanFull(),
                TextInput::make('city')->maxLength(255),
                TextInput::make('state')->maxLength(255),
                TextInput::make('pincode')->minLength(6)->maxLength(6),
            ]),
            FormSection::make('Health and Status')->columns(2)->schema([
                Textarea::make('medical_conditions')->rows(2),
                Textarea::make('allergies')->rows(2),
                Toggle::make('is_transport'),
                Toggle::make('is_hostel'),
                Select::make('status')->options([
                    'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended',
                    'graduated' => 'Graduated', 'dropped_out' => 'Dropped Out', 'transferred' => 'Transferred',
                ])->required(),
                DatePicker::make('leaving_date')->native(false),
                TextInput::make('leaving_reason')->maxLength(255),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')->circular(),
                TextColumn::make('admission_no')->searchable()->sortable()->badge(),
                TextColumn::make('full_name')->label('Student Name')->searchable(['first_name', 'middle_name', 'last_name'])->sortable(),
                TextColumn::make('class.name')->label('Class')->sortable(),
                TextColumn::make('section.name')->label('Section'),
                TextColumn::make('house.name')->label('House')->badge()->placeholder('—'),
                TextColumn::make('roll_no')->sortable(),
                TextColumn::make('gender')->badge(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('admission_date')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('class_id')->relationship('class', 'name'),
                SelectFilter::make('section_id')->relationship('section', 'name'),
                SelectFilter::make('status')->options([
                    'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended',
                    'graduated' => 'Graduated', 'dropped_out' => 'Dropped Out', 'transferred' => 'Transferred',
                ]),
                SelectFilter::make('gender')->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other']),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('report_card')
                    ->label('📄 Report Card')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->schema([
                        Select::make('exam_id')
                            ->label('Select Exam')
                            ->options(fn (Student $record) => Exam::query()
                                ->where('school_id', $record->school_id)
                                ->where('result_published', true)
                                ->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (Student $record, array $data) {
                        return redirect()->route('report.card', [
                            'student' => $record->id,
                            'exam' => $data['exam_id'],
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('promote')
                        ->label('Promote to Class')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->requiresConfirmation()
                        ->form([
                            Select::make('class_id')
                                ->label('New Class')
                                ->options(fn (): array => SchoolClass::query()->orderBy('sort_order')->pluck('name', 'id')->all())
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $newClass = SchoolClass::query()->findOrFail($data['class_id']);

                            if ($records->contains(fn (Student $student): bool => $student->school_id !== $newClass->school_id)) {
                                throw ValidationException::withMessages([
                                    'class_id' => 'The selected class must belong to the same school as every selected student.',
                                ]);
                            }

                            $records->each->update(['class_id' => $newClass->id]);
                        }),
                    BulkAction::make('assign_house')
                        ->label('Assign to House')
                        ->icon('heroicon-o-flag')
                        ->requiresConfirmation()
                        ->form([
                            Select::make('house_id')
                                ->label('House')
                                ->relationship('house', 'name')
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['house_id' => $data['house_id']]);
                        }),
                ]),
            ])
            ->defaultSort('admission_no', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
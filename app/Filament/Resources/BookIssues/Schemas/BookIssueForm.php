<?php

namespace App\Filament\Resources\BookIssues\Schemas;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BookIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';
        $isStudent = fn (Get $get): bool => $get('member_type') === 'student';
        $isStaff = fn (Get $get): bool => $get('member_type') === 'staff';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Issue Book')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        Select::make('book_id')
                            ->label('Select Book')
                            ->relationship('book', 'title', fn (Builder $query, ?BookIssue $record) => $query->where(fn (Builder $q) => $q
                                ->where('available_copies', '>', 0)
                                ->when($record?->book_id, fn (Builder $q, $bookId) => $q->orWhereKey($bookId))))
                            ->getOptionLabelFromRecordUsing(fn (Book $record): string => "{$record->title} (Available: {$record->available_copies}) - ISBN: {$record->isbn_no}")
                            ->searchable(['title', 'isbn_no'])
                            ->preload()
                            ->required(),

                        Select::make('member_type')
                            ->label('Issued To (Member Type)')
                            ->options([
                                'student' => 'Student',
                                'staff' => 'Staff / Teacher',
                            ])
                            ->default('student')
                            ->live()
                            ->required(),

                        Select::make('student_id')
                            ->label('Select Student')
                            ->relationship('student', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $record): string => "{$record->full_name} (Adm: {$record->admission_no})")
                            ->searchable(['first_name', 'last_name', 'admission_no'])
                            ->preload()
                            ->visible($isStudent)
                            ->required($isStudent),

                        Select::make('user_id')
                            ->label('Select Staff Member')
                            ->relationship('staff', 'name', fn (Builder $query) => $query
                                ->sameSchool()
                                ->whereIn('user_type', StaffResource::STAFF_TYPES))
                            ->searchable()
                            ->preload()
                            ->visible($isStaff)
                            ->required($isStaff),

                        DatePicker::make('issue_date')
                            ->label('Issue Date')
                            ->default(now())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->default(now()->addDays(14))
                            ->afterOrEqual('issue_date')
                            ->required(),
                    ])->columns(3),

                Section::make('Return & Fine Details')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'issued' => 'Issued / Borrowed',
                                'returned' => 'Returned',
                                'lost' => 'Lost',
                                'damaged' => 'Damaged',
                            ])
                            ->default('issued')
                            ->required(),

                        DatePicker::make('return_date')
                            ->label('Actual Return Date')
                            ->afterOrEqual('issue_date'),

                        TextInput::make('fine_amount')
                            ->label('Late Fine (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->default(0.00),

                        Select::make('fine_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'waived' => 'Waived',
                            ])
                            ->default('unpaid'),

                        Textarea::make('remarks')
                            ->columnSpanFull(),
                    ])->columns(4),
            ]);
    }
}

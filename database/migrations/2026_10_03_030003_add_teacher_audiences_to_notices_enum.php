<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_VALUES = ['all', 'staff', 'students', 'guardians', 'specific_class'];

    private const NEW_VALUES = [...self::OLD_VALUES, 'teachers', 'class_teacher'];

    public function up(): void
    {
        // SQLite (local/testing) stores enums as a CHECK constraint baked into the table's
        // original CREATE TABLE statement; the application layer (Filament forms) already
        // constrains valid values, so it's safe to only widen the real MySQL enum here.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $values = collect(self::NEW_VALUES)->map(fn (string $value): string => "'{$value}'")->implode(',');
        DB::statement("ALTER TABLE notices MODIFY COLUMN target_audience ENUM({$values}) NOT NULL DEFAULT 'all'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $values = collect(self::OLD_VALUES)->map(fn (string $value): string => "'{$value}'")->implode(',');
        DB::statement("ALTER TABLE notices MODIFY COLUMN target_audience ENUM({$values}) NOT NULL DEFAULT 'all'");
    }
};

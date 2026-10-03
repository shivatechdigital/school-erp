<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_TYPES = ['super_admin', 'school_admin', 'branch_admin', 'teacher', 'accountant', 'librarian', 'transport_manager', 'receptionist', 'parent', 'student', 'driver'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'hr', 'doctor'];

    public function up(): void
    {
        // SQLite (local/testing) stores enums as a CHECK constraint baked into the table's
        // original CREATE TABLE statement; altering it needs a full table rebuild, and the
        // application layer (Filament forms) already constrains valid values, so it's safe
        // to only widen the real MySQL enum here.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $values = collect(self::NEW_TYPES)->map(fn (string $type): string => "'{$type}'")->implode(',');
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM({$values}) NOT NULL DEFAULT 'teacher'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $values = collect(self::OLD_TYPES)->map(fn (string $type): string => "'{$type}'")->implode(',');
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM({$values}) NOT NULL DEFAULT 'teacher'");
    }
};

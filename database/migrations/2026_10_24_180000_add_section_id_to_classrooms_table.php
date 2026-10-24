<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table): void {
            $table->foreignId('section_id')->nullable()->after('branch_id')
                ->constrained()->nullOnDelete();
            $table->unique('section_id');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table): void {
            $table->dropUnique(['section_id']);
            $table->dropConstrainedForeignId('section_id');
        });
    }
};

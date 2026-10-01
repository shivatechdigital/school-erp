<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', [
                'present', 'absent', 'late', 'half_day', 'leave', 'holiday',
            ])->default('present');
            $table->integer('period_number')->nullable(); // null = full day
            $table->text('remark')->nullable();
            $table->foreignId('marked_by')->constrained('users');
            $table->timestamp('marked_at')->useCurrent();
            $table->timestamps();

            $table->unique(
                ['student_id', 'date', 'period_number'],
                'unique_student_attendance'
            );

            $table->index(['school_id', 'branch_id', 'date']);
            $table->index(['class_id', 'section_id', 'date']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};

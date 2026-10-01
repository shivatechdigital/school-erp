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
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->date('assigned_date');
            $table->date('due_date');
            $table->unsignedSmallInteger('max_marks')->default(100);
            $table->json('attachments')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'class_id', 'section_id', 'due_date']);
        });

        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('homework_id')->constrained('homework')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            $table->dateTime('submitted_at');
            $table->json('submitted_files')->nullable();
            $table->text('student_notes')->nullable();

            $table->enum('status', ['submitted', 'late', 'evaluated', 'resubmit_required'])->default('submitted');
            $table->decimal('marks_obtained', 5, 2)->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('evaluated_at')->nullable();
            $table->timestamps();

            $table->unique(['homework_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework');
    }
};

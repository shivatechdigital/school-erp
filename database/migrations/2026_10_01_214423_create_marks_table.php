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
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            $table->decimal('theory_marks', 6, 2)->nullable();
            $table->decimal('practical_marks', 6, 2)->nullable();
            $table->decimal('total_marks', 6, 2)->nullable();
            $table->decimal('max_marks', 6, 2)->default(100);
            $table->decimal('pass_marks', 6, 2)->default(33);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade', 5)->nullable();

            $table->enum('result', ['pass', 'fail', 'absent', 'withheld'])->nullable();
            $table->integer('rank_in_class')->nullable();
            $table->text('remark')->nullable();

            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['exam_id', 'student_id', 'subject_id'],
                'unique_student_exam_subject'
            );

            $table->index(['school_id', 'exam_id']);
            $table->index(['class_id', 'exam_id']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};

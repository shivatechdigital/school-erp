<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['section_id', 'teacher_id', 'valid_until'], 'attendance_grants_active_idx');
        });

        Schema::create('student_attendance_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_attendance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('access_grant_id')->nullable()->constrained('attendance_access_grants')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamp('changed_at');
            $table->timestamps();
        });

        Schema::create('timetable_exchange_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requester_timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('recipient_timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->text('response_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'status']);
            $table->index(['requester_id', 'status']);
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->foreignId('substitute_for_id')->nullable()->after('teacher_id')->constrained('users')->nullOnDelete();
            $table->foreignId('exchange_request_id')->nullable()->after('substitute_for_id')->constrained('timetable_exchange_requests')->nullOnDelete();
        });

        Schema::create('mark_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exam_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('existing_mark_id')->nullable()->constrained('marks')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_teacher_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('theory_marks', 6, 2)->nullable();
            $table->decimal('practical_marks', 6, 2)->nullable();
            $table->text('remark')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('response_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_teacher_id', 'status'], 'mark_approval_teacher_status_idx');
            $table->index(['exam_schedule_id', 'student_id']);
        });

        Schema::create('mark_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mark_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('old_values')->nullable();
            $table->json('new_values');
            $table->timestamp('changed_at');
            $table->timestamps();
        });

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->enum('visibility', ['class_teacher', 'staff'])->default('class_teacher');
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });

        Schema::create('grading_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('overall_pass_percentage', 5, 2)->default(33);
            $table->decimal('subject_pass_percentage', 5, 2)->default(33);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'branch_id']);
        });

        Schema::create('exam_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exam_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('homework_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homework')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['homework_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exchange_request_id');
            $table->dropConstrainedForeignId('substitute_for_id');
        });

        Schema::dropIfExists('homework_sections');
        Schema::dropIfExists('exam_papers');
        Schema::dropIfExists('grading_policies');
        Schema::dropIfExists('student_notes');
        Schema::dropIfExists('mark_audits');
        Schema::dropIfExists('mark_approval_requests');
        Schema::dropIfExists('timetable_exchange_requests');
        Schema::dropIfExists('student_attendance_audits');
        Schema::dropIfExists('attendance_access_grants');
    }
};
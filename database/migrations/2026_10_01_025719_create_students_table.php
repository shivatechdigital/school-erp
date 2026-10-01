<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Login account

            // Unique IDs
            $table->string('admission_no')->unique(); // ADM-2025-001
            $table->string('roll_no')->nullable();
            $table->string('gr_no')->nullable(); // General Register No
            $table->string('student_code')->nullable()->unique();

            // Personal Info
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->virtualAs("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))");
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth');
            $table->string('birth_place')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('religion')->nullable();
            $table->string('caste')->nullable();
            $table->enum('category', ['General', 'OBC', 'SC', 'ST', 'EWS', 'Other'])->default('General');
            $table->string('nationality')->default('Indian');
            $table->string('mother_tongue')->nullable();
            $table->string('aadhaar_no', 12)->nullable();
            $table->string('samagra_id')->nullable(); // Govt ID

            // Photo
            $table->string('photo')->nullable();

            // Contact
            $table->string('email')->nullable();
            $table->string('phone', 15)->nullable();

            // Address
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 6)->nullable();

            // Admission Details
            $table->date('admission_date');
            $table->string('previous_school')->nullable();
            $table->string('previous_class')->nullable();
            $table->enum('admission_type', ['new', 'transfer', 're-admission'])->default('new');

            // Medical
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();

            // Transport & Hostel
            $table->boolean('is_transport')->default(false);
            $table->boolean('is_hostel')->default(false);

            // Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'graduated', 'dropped_out', 'transferred'])->default('active');
            $table->date('leaving_date')->nullable();
            $table->string('leaving_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for fast queries
            $table->index(['school_id', 'branch_id']);
            $table->index(['class_id', 'section_id']);
            $table->index('academic_year_id');
            $table->index('admission_no');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
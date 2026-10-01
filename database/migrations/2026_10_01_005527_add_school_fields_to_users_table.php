<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Multi-tenancy
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained()->nullOnDelete();

            // Profile
            $table->string('employee_code')->nullable()->unique()->after('email');
            $table->string('phone', 15)->nullable()->after('email');
            $table->string('alternate_phone', 15)->nullable();
            $table->string('profile_photo')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 6)->nullable();

            // Staff specific
            $table->string('qualification')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();

            // Status
            $table->enum('user_type', ['super_admin', 'school_admin', 'branch_admin', 'teacher', 'accountant', 'librarian', 'transport_manager', 'receptionist', 'parent', 'student', 'driver'])->default('teacher');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('last_login_at')->nullable();

            $table->softDeletes();

            $table->index('school_id');
            $table->index('branch_id');
            $table->index('user_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['school_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'school_id', 'branch_id', 'employee_code', 'phone',
                'alternate_phone', 'profile_photo', 'gender',
                'date_of_birth', 'blood_group', 'address', 'city',
                'state', 'pincode', 'qualification', 'designation',
                'department', 'joining_date', 'salary', 'user_type',
                'status', 'last_login_at'
            ]);
        });
    }
};
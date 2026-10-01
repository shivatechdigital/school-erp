<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Parent login

            $table->string('name');
            $table->enum('relation', ['father', 'mother', 'guardian', 'other'])->default('father');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('photo')->nullable();

            // Contact
            $table->string('phone', 15);
            $table->string('alternate_phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp', 15)->nullable();

            // Occupation
            $table->string('occupation')->nullable();
            $table->string('company_name')->nullable();
            $table->decimal('annual_income', 12, 2)->nullable();

            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 6)->nullable();

            // Documents
            $table->string('aadhaar_no', 12)->nullable();
            $table->string('pan_no', 10)->nullable();

            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
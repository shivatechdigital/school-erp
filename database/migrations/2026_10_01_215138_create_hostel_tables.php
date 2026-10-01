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
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->enum('type', ['boys', 'girls', 'co_ed'])->default('boys');
            $table->string('warden_name')->nullable();
            $table->string('warden_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hostel_id')->constrained('hostels')->cascadeOnDelete();

            $table->string('room_number', 50);
            $table->enum('room_type', ['single', 'double', 'triple', 'dormitory', 'ac', 'non_ac'])->default('double');
            $table->unsignedSmallInteger('bed_capacity')->default(2);
            $table->decimal('cost_per_bed', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hostel_id', 'room_number']);
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained('hostel_rooms')->cascadeOnDelete();

            $table->string('bed_number', 20)->nullable();
            $table->date('allocated_date');
            $table->date('vacated_date')->nullable();
            $table->enum('status', ['allocated', 'vacated'])->default('allocated');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'hostel_room_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostels');
    }
};

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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('vehicle_number', 50)->unique();
            $table->string('vehicle_model', 100)->nullable();
            $table->unsignedSmallInteger('capacity');
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('driver_name', 100);
            $table->string('driver_phone', 20);
            $table->string('driver_license', 50)->nullable();
            $table->string('helper_name', 100)->nullable();
            $table->string('helper_phone', 20)->nullable();
            $table->string('gps_device_id', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            $table->string('title');
            $table->string('start_point');
            $table->string('end_point');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();

            $table->string('stop_name');
            $table->time('pickup_time')->nullable();
            $table->time('drop_time')->nullable();
            $table->decimal('monthly_fare', 8, 2)->default(0.00);
            $table->unsignedSmallInteger('stop_order')->default(1);
            $table->timestamps();
        });

        Schema::create('student_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_stop_id')->constrained()->cascadeOnDelete();

            $table->enum('trip_type', ['both', 'pickup_only', 'drop_only'])->default('both');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'route_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transports');
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('vehicles');
    }
};

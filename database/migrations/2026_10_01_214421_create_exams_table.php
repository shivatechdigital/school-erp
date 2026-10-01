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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', [
                'unit_test', 'mid_term', 'final', 'practical',
                'viva', 'assignment', 'other',
            ])->default('unit_test');
            $table->decimal('total_marks', 6, 2)->default(100);
            $table->decimal('pass_marks', 6, 2)->default(33);
            $table->decimal('weightage', 5, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', [
                'upcoming', 'ongoing', 'completed', 'cancelled',
            ])->default('upcoming');
            $table->boolean('result_published')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};

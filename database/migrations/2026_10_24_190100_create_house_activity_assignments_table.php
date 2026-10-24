<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_activity_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('house_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('incharge_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('incharge_label')->nullable(); // "Coach", "Head", "Mentor"
            $table->foreignId('captain_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();

            $table->unique(['house_activity_id', 'house_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_activity_assignments');
    }
};

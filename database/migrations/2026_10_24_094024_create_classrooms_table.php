<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room_number');
            $table->string('floor', 80);
            $table->unsignedInteger('capacity')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};

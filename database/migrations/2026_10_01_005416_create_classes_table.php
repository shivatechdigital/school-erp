<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Class 1", "Class 10", "Nursery"
            $table->string('code')->nullable(); // "C1", "C10"
            $table->integer('sort_order')->default(0); // Nursery=1, KG=2, Class1=3...
            $table->enum('group', ['pre_primary', 'primary', 'middle', 'secondary', 'senior_secondary'])->default('primary');
            $table->boolean('has_sections')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
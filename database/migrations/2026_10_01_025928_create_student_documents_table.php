<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', [
                'birth_certificate', 'aadhaar', 'photo',
                'transfer_certificate', 'marksheet',
                'caste_certificate', 'income_certificate',
                'medical_certificate', 'other'
            ]);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50)->nullable(); // pdf, jpg, png
            $table->integer('file_size')->nullable(); // in KB
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
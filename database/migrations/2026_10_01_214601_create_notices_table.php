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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('published_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');

            $table->enum('target_audience', ['all', 'staff', 'students', 'guardians', 'specific_class'])->default('all');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();

            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->json('attachments')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('send_notification')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};

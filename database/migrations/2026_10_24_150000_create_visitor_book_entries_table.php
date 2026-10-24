<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_book_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purpose');
            $table->unsignedTinyInteger('number_of_people');
            $table->json('people');
            $table->string('phone', 20);
            $table->date('visit_date');
            $table->time('in_time');
            $table->time('out_time')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'branch_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_book_entries');
    }
};

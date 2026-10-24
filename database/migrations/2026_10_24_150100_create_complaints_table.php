<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('complainant_name');
            $table->string('against_name');
            $table->date('complaint_date');
            $table->text('details');
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'branch_id', 'complaint_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};

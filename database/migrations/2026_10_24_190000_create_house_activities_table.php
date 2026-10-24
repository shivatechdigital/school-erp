<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('category'); // sports, cultural, debate, other
            $table->text('description')->nullable();
            $table->enum('status', [
                'announced', 'registration_open', 'registration_closed', 'team_announced', 'completed',
            ])->default('announced');
            $table->unsignedInteger('max_participants_per_house')->nullable();
            $table->date('registration_start_date')->nullable();
            $table->date('registration_end_date')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_activities');
    }
};

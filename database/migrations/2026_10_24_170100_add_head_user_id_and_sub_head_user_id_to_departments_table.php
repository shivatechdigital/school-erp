<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->foreignId('head_user_id')->nullable()->after('description')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('sub_head_user_id')->nullable()->after('head_user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('sub_head_user_id');
            $table->dropConstrainedForeignId('head_user_id');
        });
    }
};

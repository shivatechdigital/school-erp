<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_access_grants', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_access_grants', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};

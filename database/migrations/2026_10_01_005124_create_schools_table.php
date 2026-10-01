<?php
// database/migrations/xxxx_create_schools_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // e.g., SCH001
            $table->string('logo')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('website')->nullable();

            // Address
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 6);
            $table->string('country')->default('India');

            // School Info
            $table->enum('board', ['CBSE', 'ICSE', 'State', 'IB', 'IGCSE', 'Other'])->default('CBSE');
            $table->string('affiliation_no')->nullable();
            $table->string('recognition_no')->nullable();
            $table->enum('type', ['Boys', 'Girls', 'Co-Ed'])->default('Co-Ed');
            $table->enum('medium', ['English', 'Hindi', 'Regional', 'Bilingual'])->default('English');
            $table->year('established_year')->nullable();

            // SaaS
            $table->enum('status', ['pending', 'active', 'suspended', 'expired'])->default('pending');
            $table->string('domain')->nullable(); // custom domain
            $table->string('subdomain')->unique()->nullable(); // school1.yourerp.com
            $table->json('modules_enabled')->nullable(); // ["attendance","fee","exam"]
            $table->integer('storage_limit_mb')->default(5120); // 5GB default
            $table->integer('student_limit')->default(500);

            // Theme (White-label)
            $table->string('primary_color', 7)->default('#3B82F6');
            $table->string('secondary_color', 7)->default('#10B981');

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
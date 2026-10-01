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
        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('book_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('isbn_no', 50)->nullable();
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->string('edition', 50)->nullable();
            $table->string('rack_no', 50)->nullable();
            $table->unsignedSmallInteger('total_copies')->default(1);
            $table->unsignedSmallInteger('available_copies')->default(1);
            $table->decimal('price', 8, 2)->default(0.00);
            $table->string('cover_image')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'title', 'isbn_no']);
        });

        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();

            $table->enum('member_type', ['student', 'staff'])->default('student');
            $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();

            $table->decimal('fine_amount', 8, 2)->default(0.00);
            $table->enum('fine_status', ['unpaid', 'paid', 'waived'])->default('unpaid');
            $table->enum('status', ['issued', 'returned', 'lost', 'damaged'])->default('issued');
            $table->text('remarks')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_issues');
        Schema::dropIfExists('books');
        Schema::dropIfExists('book_categories');
    }
};

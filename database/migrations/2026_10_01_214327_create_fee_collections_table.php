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
        Schema::create('fee_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receipt_no')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_mode', [
                'cash', 'cheque', 'dd', 'upi', 'card', 'net_banking', 'online',
            ])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->date('payment_date');
            $table->text('remark')->nullable();
            $table->foreignId('collected_by')->constrained('users');
            $table->enum('status', [
                'success', 'pending', 'failed', 'refunded',
            ])->default('success');
            $table->timestamps();

            $table->index(['school_id', 'branch_id']);
            $table->index('student_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_collections');
    }
};

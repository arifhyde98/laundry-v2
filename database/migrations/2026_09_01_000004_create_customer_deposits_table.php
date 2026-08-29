<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['topup', 'order_deduction', 'refund'])->default('topup');
            $table->decimal('balance_after', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, qris, transfer
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_deposits');
    }
};


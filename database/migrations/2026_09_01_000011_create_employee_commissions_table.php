<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('activity', ['washing', 'drying', 'ironing', 'packing', 'delivery'])->default('washing');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('rate_per_unit', 10, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_commissions');
    }
};


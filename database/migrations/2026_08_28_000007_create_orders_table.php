<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code')->unique(); // e.g. INV-20260830-0001
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // kasir pembuat
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('rack_id')->nullable()->constrained('racks')->nullOnDelete(); // lokasi rak saat selesai
            
            // Financials
            $table->decimal('total_weight_qty', 8, 2)->default(0);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            
            // Statuses
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'deposit', 'qris', 'transfer'])->default('cash');
            $table->enum('order_status', [
                'received',       // diterima kasir
                'washing',        // sedang dicuci
                'drying',         // pengeringan
                'ironing',        // setrika
                'packing',        // dipacking
                'ready',          // siap diambil (di rak)
                'completed',      // diambil pelanggan / selesai
                'cancelled'       // dibatalkan
            ])->default('received');

            $table->date('order_date');
            $table->dateTime('estimated_completion')->nullable();
            $table->dateTime('actual_completion')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};


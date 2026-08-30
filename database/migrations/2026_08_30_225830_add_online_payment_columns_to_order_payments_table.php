<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_gateway_id')->nullable()->after('payment_method');
            $table->string('gateway_transaction_id')->nullable()->after('payment_gateway_id'); // ID from gateway
            $table->text('payment_url')->nullable()->after('gateway_transaction_id'); // Link to pay or QR string
            $table->enum('payment_status', ['pending', 'success', 'failed', 'expired'])->default('success')->after('payment_url'); // Default success for backward compatibility with manual cash
            $table->json('gateway_response')->nullable()->after('payment_status'); // Store raw response for debugging
            
            $table->foreign('payment_gateway_id')->references('id')->on('payment_gateways')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropForeign(['payment_gateway_id']);
            $table->dropColumn([
                'payment_gateway_id',
                'gateway_transaction_id',
                'payment_url',
                'payment_status',
                'gateway_response'
            ]);
        });
    }
};

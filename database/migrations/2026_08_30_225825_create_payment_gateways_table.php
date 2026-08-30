<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. midtrans, xendit, tripay
            $table->string('display_name'); // e.g. Midtrans Payment Gateway
            $table->boolean('is_active')->default(false);
            $table->enum('mode', ['sandbox', 'production'])->default('sandbox');
            
            // Credentials
            $table->string('server_key')->nullable();
            $table->string('client_key')->nullable();
            $table->string('merchant_id')->nullable();
            
            // Additional configs (JSON for flexibility)
            $table->json('additional_config')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Deterjen Cair, Parfum Lily, Plastik 35x50
            $table->string('category')->default('chemical'); // chemical, packaging, equipment
            $table->decimal('stock', 10, 2)->default(0);
            $table->string('unit')->default('ml'); // ml, pcs, kg, roll
            $table->decimal('minimum_stock', 10, 2)->default(100);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('chemical_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('dosage_per_kg', 8, 2); // e.g. 25 ml per 1 kg
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->string('category')->default('operational'); // operational, supplies, utility, maintenance, salary, other
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('receipt_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('chemical_recipes');
        Schema::dropIfExists('inventory_items');
    }
};


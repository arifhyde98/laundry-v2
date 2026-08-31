<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->string('name'); // Kiloan Reguler, Kiloan Express, Cuci Bedcover, Sepatu
            $table->enum('unit', ['kg', 'pcs', 'meter', 'pasang'])->default('kg');
            $table->decimal('price', 12, 2);
            $table->integer('estimated_hours')->default(72); // durasi pengerjaan dalam jam
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};


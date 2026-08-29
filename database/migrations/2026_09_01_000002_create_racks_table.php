<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->string('rack_code')->unique(); // contoh: RAK-A1, GANTUNG-01
            $table->string('category')->default('regular'); // regular, hanger, express, shoes
            $table->integer('capacity')->default(5);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('racks');
    }
};


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
        Schema::table('outlets', function (Blueprint $table) {
            $table->decimal('commission_washing', 10, 2)->default(500);
            $table->decimal('commission_ironing', 10, 2)->default(1000);
            $table->decimal('commission_packing', 10, 2)->default(200);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['commission_washing', 'commission_ironing', 'commission_packing']);
        });
    }
};

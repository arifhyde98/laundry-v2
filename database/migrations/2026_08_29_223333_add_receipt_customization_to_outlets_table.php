<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->string('receipt_header')->nullable()->after('address');
            $table->text('receipt_footer')->nullable()->after('receipt_header');
            $table->string('receipt_paper_size', 10)->default('58mm')->after('receipt_footer');
            $table->string('logo_url')->nullable()->after('receipt_paper_size');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['receipt_header', 'receipt_footer', 'receipt_paper_size', 'logo_url']);
        });
    }
};

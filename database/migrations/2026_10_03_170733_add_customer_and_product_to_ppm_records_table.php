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
        Schema::table('ppm_records', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->after('customer_id')->constrained('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppm_records', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};

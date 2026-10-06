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
        Schema::create('ppm_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_type')->default('Installation / PPM');
            $table->date('entry_date')->nullable();
            $table->string('purchase_order_no')->nullable();
            $table->string('supply_against')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('product_description')->nullable();
            $table->string('equipment_name')->nullable();
            $table->string('model_make')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('department')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_no')->nullable();
            $table->date('installation_date')->nullable();
            $table->string('performed_by')->nullable();
            $table->string('service_type')->default('New Installation');
            $table->text('scope_of_work')->nullable();
            $table->date('next_ppm_date')->nullable();
            $table->string('status')->default('Completed');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppm_records');
    }
};

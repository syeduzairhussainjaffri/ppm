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
        // 1. Create Branches Table
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('city')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add branch_id to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('role')->constrained('branches')->nullOnDelete();
            });
        }

        // 3. Add branch_id to ppm_records table
        if (Schema::hasTable('ppm_records') && !Schema::hasColumn('ppm_records', 'branch_id')) {
            Schema::table('ppm_records', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('product_id')->constrained('branches')->nullOnDelete();
            });
        }

        // 4. Add branch_id to customers table
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'branch_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'branch_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('ppm_records') && Schema::hasColumn('ppm_records', 'branch_id')) {
            Schema::table('ppm_records', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        Schema::dropIfExists('branches');
    }
};

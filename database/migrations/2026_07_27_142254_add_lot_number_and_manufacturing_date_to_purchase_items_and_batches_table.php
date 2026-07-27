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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('lot_number')->nullable()->after('quantity');
            $table->date('manufacturing_date')->nullable()->after('expiry_date');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->string('lot_number')->nullable()->after('quantity');
            $table->date('manufacturing_date')->nullable()->after('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['lot_number', 'manufacturing_date']);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['lot_number', 'manufacturing_date']);
        });
    }
};

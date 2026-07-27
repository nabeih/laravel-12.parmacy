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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales');
            $table->string('payment_method');
            $table->enum('transaction_id', ['bank_transfer', 'palbay', 'jawalbay'])->nullable();
            $table->string('reference_number')->nullable();
            $table->string('sender_name')->nullable();
            $table->date('payment_date');
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};

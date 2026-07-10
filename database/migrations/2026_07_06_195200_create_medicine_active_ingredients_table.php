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
        Schema::create('medicine_active_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('active_ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('strength_value', 8, 2);
            $table->string('strength_unit', 20);
            $table->unique(['medicine_id', 'active_ingredient_id'], 'med_ing_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_active_ingredients');
    }
};

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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            // Brand Names
            $table->string('brand_name_en');
            $table->string('brand_name_ar');

            // Relationships
            $table->foreignId('manufacturer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('medicine_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('dosage_form_id')->constrained('dosage_forms')->cascadeOnUpdate()->restrictOnDelete();

            // Pricing
            $table->decimal('reference_price', 10, 2)->default(0);

            // Product Information
            $table->string('barcode')->nullable()->unique();
            $table->boolean('requires_prescription')->default(false);

            // Descriptions
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('notes')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};

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
        Schema::create('medicine_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained('pharmacies');
            $table->foreignId('requested_by')->constrained('users');

            $table->string('brand_name_en');
            $table->string('brand_name_ar');
            $table->foreignId('manufacturer_id')->constrained('manufacturers');
            $table->foreignId('category_id')->constrained('medicine_categories');
            $table->foreignId('dosage_form_id')->constrained('dosage_forms');
            $table->decimal('reference_price', 10, 2)->default(0);
            $table->string('barcode')->nullable();
            $table->boolean('requires_prescription')->default(false);
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_requests');
    }
};

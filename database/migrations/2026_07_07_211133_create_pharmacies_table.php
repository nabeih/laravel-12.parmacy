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

        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacist_id')->unique()->constrained('pharmacists');
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('phone', 13);
            $table->string('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();
            $table->string('logo')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->enum('status', ['opne', 'closed', 'suspended'])->default('opne');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->SoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};

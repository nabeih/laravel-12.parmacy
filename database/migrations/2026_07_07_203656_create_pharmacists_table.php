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
        //         id
        // user_id
        // national_id
        // syndicate_number
        // license_number
        // graduation_university
        // graduation_year
        // certificate_file
        // syndicate_file
        // license_file
        // status
        // approved_at
        // notes
        // created_at
        // updated_at

        Schema::create('pharmacists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('national_id')->unique();
            $table->string('syndicate_number')->unique();
            $table->string('license_number')->unique();
            $table->string('graduation_university');
            $table->year('graduation_year');
            $table->string('certificate_file');
            $table->string('syndicate_file');
            $table->string('license_file');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
            $table->SoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacists');
    }
};

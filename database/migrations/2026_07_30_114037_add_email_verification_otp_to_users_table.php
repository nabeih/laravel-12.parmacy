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
        Schema::table('users', function (Blueprint $table) {
            // Hashed OTP value — never stored in plain text.
            $table->string('email_verification_otp_hash')->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_otp_expires_at')->nullable()->after('email_verification_otp_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verification_otp_hash', 'email_verification_otp_expires_at']);
        });
    }
};

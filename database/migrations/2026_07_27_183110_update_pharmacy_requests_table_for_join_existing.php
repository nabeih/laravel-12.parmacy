<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pharmacist now submits one of these per job change (register a new
     * pharmacy, or ask to take over an existing vacant one), not once ever —
     * so the old "one request per pharmacist forever" unique constraint has
     * to go (the app-level guard in PharmacyRequestController is the real
     * gate: at most one *pending* request at a time). Also adds the
     * join-existing-pharmacy path and relaxes the new-pharmacy fields to
     * nullable, since they don't apply when joining an existing one.
     */
    public function up(): void
    {
        // MySQL won't drop a unique index that's still backing a foreign key,
        // so the FK has to go first, then the index, then a plain (non-unique)
        // FK is put back.
        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->dropForeign(['pharmacist_id']);
        });

        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->dropUnique(['pharmacist_id']);
        });

        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->foreign('pharmacist_id')->references('id')->on('pharmacists');
            $table->foreignId('target_pharmacy_id')->nullable()->after('pharmacist_id')->constrained('pharmacies')->nullOnDelete();
        });

        DB::statement('ALTER TABLE pharmacy_requests MODIFY name_ar VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pharmacy_requests MODIFY name_en VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pharmacy_requests MODIFY phone VARCHAR(13) NULL');
        DB::statement('ALTER TABLE pharmacy_requests MODIFY address VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->dropForeign(['target_pharmacy_id']);
            $table->dropColumn('target_pharmacy_id');
            $table->dropForeign(['pharmacist_id']);
        });

        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->unique('pharmacist_id');
            $table->foreign('pharmacist_id')->references('id')->on('pharmacists');
        });

        DB::statement('ALTER TABLE pharmacy_requests MODIFY name_ar VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE pharmacy_requests MODIFY name_en VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE pharmacy_requests MODIFY phone VARCHAR(13) NOT NULL');
        DB::statement('ALTER TABLE pharmacy_requests MODIFY address VARCHAR(255) NOT NULL');
    }
};

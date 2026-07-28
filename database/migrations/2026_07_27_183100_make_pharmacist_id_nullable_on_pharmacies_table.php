<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pharmacy can be briefly vacant (its pharmacist-in-charge left and no
     * one has taken over yet), so this can no longer be required. Uses raw
     * SQL for the column-nullability change since doctrine/dbal (needed by
     * Schema::change()) isn't installed in this project.
     */
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropForeign(['pharmacist_id']);
        });

        DB::statement('ALTER TABLE pharmacies MODIFY pharmacist_id BIGINT UNSIGNED NULL');

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->foreign('pharmacist_id')->references('id')->on('pharmacists')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropForeign(['pharmacist_id']);
        });

        DB::statement('ALTER TABLE pharmacies MODIFY pharmacist_id BIGINT UNSIGNED NOT NULL');

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->foreign('pharmacist_id')->references('id')->on('pharmacists');
        });
    }
};

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
        Schema::table('consultations', function (Blueprint $table) {
            // Add the column only if it doesn’t already exist
            if (!Schema::hasColumn('consultations', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('doctor_id');

                $table->foreign('location_id')
                      ->references('id')
                      ->on('locations')
                      ->onDelete('set null'); // If a location is deleted, nullify this field
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (Schema::hasColumn('consultations', 'location_id')) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            }
        });
    }
};

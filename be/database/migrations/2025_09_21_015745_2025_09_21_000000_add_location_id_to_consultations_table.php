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
            // add location_id (nullable in case old records don't have one yet)
            $table->unsignedBigInteger('location_id')->nullable()->after('doctor_id');

            // add foreign key constraint
            $table->foreign('location_id')
                  ->references('id')
                  ->on('locations')
                  ->onDelete('set null'); // if location deleted, null out
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};

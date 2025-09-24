<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMedicalHistoriesLinkToConsultations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            // Drop old FK to patient_details/patients if it exists
            if (Schema::hasColumn('medical_histories', 'patient_id')) {
                // Try generic drop; if your FK name is custom, drop by name instead.
                try {
                    $table->dropForeign(['patient_id']);
                } catch (\Throwable $e) {
                    // no-op: FK might already be gone / named differently
                }
                $table->dropColumn('patient_id');
            }

            // Add the new consultation_id column + FK
            $table->unsignedBigInteger('consultation_id')->after('id');
            $table->foreign('consultation_id')
                ->references('id')
                ->on('consultations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            // Remove the new FK/column
            try {
                $table->dropForeign(['consultation_id']);
            } catch (\Throwable $e) {
                // ignore
            }
            $table->dropColumn('consultation_id');

            // Restore patient_id column + FK (adjust to your real table)
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients') // or 'patient_details' if you want the old behavior
                ->onDelete('cascade');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterConsultationsPatientFkToPatients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Drop existing FK if it points to patient_details
            // Adjust the constraint name if different in your DB
            $table->dropForeign(['patient_id']);

            // Re-add FK to patients(id)
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);

            // Revert back to patient_details if you ever roll back
            $table->foreign('patient_id')
                ->references('id')
                ->on('patient_details')
                ->onDelete('cascade');
        });
    }
}

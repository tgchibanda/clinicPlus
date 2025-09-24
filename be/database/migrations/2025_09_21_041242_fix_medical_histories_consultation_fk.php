<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixMedicalHistoriesConsultationFk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            // 1) Drop existing FK if it exists (name may vary)
            try {
                $table->dropForeign(['consultation_id']);
            } catch (\Throwable $e) {
                // ignore if it didn't exist
            }

            // 2) Ensure the column exists and is nullable unsigned big int
            if (!Schema::hasColumn('medical_histories', 'consultation_id')) {
                $table->unsignedBigInteger('consultation_id')->nullable()->after('id');
            } else {
                $table->unsignedBigInteger('consultation_id')->nullable()->change();
            }
        });

        // 3) Clean orphaned values (MySQL syntax)
        //    Any consultation_id that doesn't match consultations.id becomes NULL
        DB::statement("
            UPDATE medical_histories mh
            LEFT JOIN consultations c ON c.id = mh.consultation_id
            SET mh.consultation_id = NULL
            WHERE mh.consultation_id IS NOT NULL AND c.id IS NULL
        ");

        // 4) Re-add FK (nullable column; only non-null values must match)
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->foreign('consultation_id')
                ->references('id')->on('consultations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            try {
                $table->dropForeign(['consultation_id']);
            } catch (\Throwable $e) {
                // ignore
            }
            // Keep the column; if you truly want to remove:
            // $table->dropColumn('consultation_id');
        });
    }
}

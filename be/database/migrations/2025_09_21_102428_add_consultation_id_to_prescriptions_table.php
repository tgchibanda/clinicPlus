<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsultationIdToPrescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Add column after patient_id (adjust position to your schema if you like)
            $table->unsignedBigInteger('consultation_id')->nullable()->after('patient_id');

            // FK -> consultations.id
            $table->foreign('consultation_id', 'prescriptions_consultation_id_foreign')
                  ->references('id')->on('consultations')
                  ->onDelete('set null'); // or ->onDelete('cascade') if you prefer
        });
    }

    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Drop FK then column (order matters)
            $table->dropForeign('prescriptions_consultation_id_foreign');
            $table->dropColumn('consultation_id');
        });
    }
}

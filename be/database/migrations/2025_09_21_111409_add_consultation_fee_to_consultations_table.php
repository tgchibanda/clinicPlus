<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsultationFeeToConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Use decimal for money; adjust precision/scale if you prefer
            $table->decimal('consultation_fee', 10, 2)->nullable()->after('instruction');
        });
    }

    public function down()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn('consultation_fee');
        });
    }
}

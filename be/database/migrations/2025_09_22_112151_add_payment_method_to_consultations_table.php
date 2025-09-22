<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodToConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
}

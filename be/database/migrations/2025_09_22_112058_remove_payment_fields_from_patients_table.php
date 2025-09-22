<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePaymentFieldsFromPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('patients', 'voucher_code')) {
                $table->dropColumn('voucher_code');
            }
            if (Schema::hasColumn('patients', 'payment_amount')) {
                $table->dropColumn('payment_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('payment_method')->nullable();
            $table->string('voucher_code')->nullable();
            $table->decimal('payment_amount', 10, 2)->nullable();
        });
    }
}

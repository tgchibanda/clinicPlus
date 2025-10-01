<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteToInsurancePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable text 'note' column to insurance_payments.
     */
    public function up()
    {
        Schema::table('insurance_payments', function (Blueprint $table) {
            // add the column if it doesn't already exist
            if (!Schema::hasColumn('insurance_payments', 'note')) {
                $table->text('note')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'note' column.
     */
    public function down()
    {
        Schema::table('insurance_payments', function (Blueprint $table) {
            if (Schema::hasColumn('insurance_payments', 'note')) {
                $table->dropColumn('note');
            }
        });
    }
}

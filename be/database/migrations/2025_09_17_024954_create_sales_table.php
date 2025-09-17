<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('prescription_id')->nullable();
            $table->unsignedBigInteger('pharmacist_id');
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'voucher']);
            $table->string('voucher_code')->nullable();
            $table->timestamps();
            
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('prescription_id')->references('id')->on('prescriptions');
            $table->foreign('pharmacist_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consultation_id')->nullable(false)->index();
            $table->unsignedBigInteger('insurance_id')->nullable()->index(); // points to insurance subscription id (if any)
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_method', 64)->nullable();
            $table->string('transaction_ref', 128)->nullable();
            $table->text('raw_payload')->nullable();
            $table->string('status', 32)->default('processed')->nullable();
            $table->timestamps();

            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('cascade');
            // Do not add foreign key for insurance_id if your insurance subscription table naming or lifecycle differs.
            // If you want:
            // $table->foreign('insurance_id')->references('id')->on('insurance_subscriptions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_payments');
    }
}

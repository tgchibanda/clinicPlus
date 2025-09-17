<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->enum('payment_method', ['cash', 'voucher']);
            $table->string('voucher_code')->nullable();
            $table->decimal('payment_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('assigned_doctor_id')->nullable();
            $table->enum('status', ['waiting', 'consulting', 'completed'])->default('waiting');
            $table->timestamp('visit_date')->useCurrent();
            $table->timestamps();
            
            $table->foreign('assigned_doctor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
}

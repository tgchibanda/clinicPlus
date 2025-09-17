<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('reason')->nullable();
            $table->string('instruction')->nullable();
            $table->string('doctor_notes')->nullable();
            $table->string('examination')->nullable();
            $table->string('diagnosis')->nullable();
            $table->string('investigation')->nullable();
            $table->string('management')->nullable();
            $table->string('status')->nullable();
            $table->string('request_forms')->nullable();
            $table->foreign('patient_id')->references('id')->on('patient_details')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultations');
    }
}

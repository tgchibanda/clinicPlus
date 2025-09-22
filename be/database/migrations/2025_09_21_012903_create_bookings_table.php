<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('location_id');

            // date + time range
            $table->date('date');
            $table->time('starts_at');      // 30-min granularity (e.g., 09:00)
            $table->time('ends_at');        // starts_at + 30 min by convention

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                  ->default('confirmed');

            $table->text('notes')->nullable();

            $table->timestamps();

            // FKs
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('restrict');

            // Prevent double bookings of same doctor at same time
            $table->unique(['doctor_id', 'date', 'starts_at'], 'doctor_slot_unique');

            // You can also speed up lookups:
            $table->index(['date', 'doctor_id']);
            $table->index(['date', 'location_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}

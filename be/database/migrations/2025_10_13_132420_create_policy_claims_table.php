<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolicyClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->unsignedBigInteger('consultation_id')->nullable()->index();
            // Who the claim paid for (owner or dependent details) - stored as JSON for flexibility
            $table->string('claim_holder_first_name')->nullable();
            $table->string('claim_holder_last_name')->nullable();
            $table->date('claim_holder_dob')->nullable();
            $table->string('claim_holder_relationship')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->string('status')->default('processed'); // processed, pending, rejected etc.
            $table->timestamps();

            // If you have foreign keys:
            $table->foreign('subscription_id')->references('id')->on('insurance_subscriptions')->onDelete('cascade');
            // consultations table foreign key if present:
            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('policy_claims');
    }
}

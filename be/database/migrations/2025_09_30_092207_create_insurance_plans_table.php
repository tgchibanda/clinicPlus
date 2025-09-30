<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsurancePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('insurance_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_adult', 10, 2);
            $table->decimal('price_child', 10, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('active');
        });

        Schema::create('insurance_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained('insurance_plans')->onDelete('set null');
            $table->enum('status', ['pending', 'active', 'lapsed', 'closed'])->default('pending');
            $table->datetime('started_at');
            $table->datetime('coverage_starts_at')->nullable();
            $table->datetime('first_payment_at')->nullable();
            $table->datetime('last_payment_at')->nullable();
            $table->decimal('total_paid_amount', 12, 2)->default(0.00);
            $table->integer('due_count')->default(0);
            $table->date('next_due_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('status');
            $table->index('next_due_date');
            $table->index(['status', 'next_due_date']);
        });

        Schema::create('insurance_dependents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('insurance_subscriptions')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->foreignId('plan_id')->constrained('insurance_plans')->onDelete('restrict');
            $table->string('relationship')->nullable();
            $table->timestamps();

            $table->index('subscription_id');
        });

        Schema::create('insurance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('insurance_subscriptions')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('transaction_ref')->nullable();
            $table->datetime('paid_at');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('subscription_id');
            $table->index('paid_at');
            $table->index(['subscription_id', 'paid_at']);
        });

        Schema::create('insurance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('insurance_subscriptions')->onDelete('cascade');
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamp('created_at');

            $table->index('subscription_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_events');
        Schema::dropIfExists('insurance_payments');
        Schema::dropIfExists('insurance_dependents');
        Schema::dropIfExists('insurance_subscriptions');
        Schema::dropIfExists('insurance_plans');
    }
}

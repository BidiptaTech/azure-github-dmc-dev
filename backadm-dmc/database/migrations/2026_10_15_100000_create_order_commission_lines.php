<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable per-item commission record for a collected payment.
 * Travclicks sees API cost and its own commission. DMC does not.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_commission_lines')) {
            return;
        }

        Schema::create('order_commission_lines', function (Blueprint $table) {
            $table->id();
            $table->uuid('line_uuid')->unique();
            $table->unsignedBigInteger('tour_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('payment_attempt_uuid', 64)->nullable();
            $table->string('payment_method', 32)->nullable();
            $table->string('channel', 16);
            $table->string('supplier_api', 64)->nullable();
            $table->string('item_label', 255)->nullable();
            $table->char('currency_code', 3);
            $table->decimal('api_cost', 18, 4)->nullable();
            $table->decimal('travclicks_commission', 18, 4)->default(0);
            $table->decimal('dmc_visible_price', 18, 4)->default(0);
            $table->decimal('dmc_commission', 18, 4)->default(0);
            $table->decimal('agent_price', 18, 4)->default(0);
            $table->decimal('charged_amount', 18, 4)->default(0);
            $table->decimal('conversion_charge', 18, 4)->default(0);
            $table->string('agent_entry', 16)->default('debit');
            $table->string('dmc_entry', 16)->default('credit');
            $table->string('stripe_transfer_id', 128)->nullable();
            $table->string('transfer_status', 32)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tour_id', 'channel']);
            $table->index('payment_attempt_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_commission_lines');
    }
};

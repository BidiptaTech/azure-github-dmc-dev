<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-DMC dated offer prices (always SGD for Travclicks).
 * dmc_id stores users.userId of the chosen DMC.
 * See docs/DMC_INVOICE_AND_OFFER_PRICING.md
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dmc_offer')) {
            return;
        }

        Schema::create('dmc_offer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dmc_offer_id')->nullable()->unique();
            $table->unsignedBigInteger('dmc_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('adult_price', 12, 2);
            $table->decimal('child_price', 12, 2);
            $table->decimal('infant_price', 12, 2);
            $table->char('currency', 3)->default('SGD');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['dmc_id', 'start_date', 'end_date'], 'dmc_offer_dmc_dates_idx');

            $table->foreign('dmc_id')
                ->references('userId')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('created_by')
                ->references('userId')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dmc_offer');
    }
};

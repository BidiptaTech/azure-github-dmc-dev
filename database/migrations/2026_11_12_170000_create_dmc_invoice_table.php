<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Travclicks-level base invoice prices (always SGD).
 * See docs/DMC_INVOICE_AND_OFFER_PRICING.md
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dmc_invoice')) {
            return;
        }

        Schema::create('dmc_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dmc_invoice_id')->nullable()->unique();
            $table->decimal('adult_price', 12, 2);
            $table->decimal('child_price', 12, 2);
            $table->decimal('infant_price', 12, 2);
            $table->char('currency', 3)->default('SGD');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('userId')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dmc_invoice');
    }
};

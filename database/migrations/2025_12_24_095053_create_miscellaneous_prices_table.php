<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('miscellaneous_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mis_id');
            $table->unsignedBigInteger('dmc_id');
            $table->decimal('adult_price', 10, 2)->default(0);
            $table->decimal('child_price', 10, 2)->default(0);
            $table->decimal('infant_price', 10, 2)->default(0);
            $table->decimal('adult_cost', 10, 2)->default(0)->nullable();
            $table->decimal('child_cost', 10, 2)->default(0)->nullable();
            $table->decimal('infant_cost', 10, 2)->default(0)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Inactive');
            $table->timestamps();
            
            $table->foreign('mis_id')->references('mis_id')->on('miscellaneous_items')->onDelete('cascade');
            $table->index(['dmc_id', 'mis_id']);
            $table->index('status');
            $table->unique(['mis_id', 'dmc_id'], 'unique_mis_dmc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miscellaneous_prices');
    }
};

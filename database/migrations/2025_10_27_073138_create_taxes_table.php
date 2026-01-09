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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id('tax_id');
            $table->string('tax_name');
            $table->enum('tax_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('tax_value', 10, 2)->default(0);
            $table->enum('calculate_on', ['subtotal', 'service_charge', 'total'])->default('subtotal');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('dmc_id');
            $table->boolean('is_active')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('dmc_id');
            $table->index('country');
            $table->index('city');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};

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
        Schema::create('vehicle_zone_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_id');
            $table->string('from_zone_id');
            $table->string('to_zone_id');
            $table->decimal('private_price', 10, 2)->default(0);
            $table->decimal('shared_price', 10, 2)->default(0);
            $table->timestamps();
            
            // Composite unique constraint
            $table->unique(['vehicle_id', 'from_zone_id', 'to_zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_zone_mappings');
    }
};

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
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transport_id')->unsigned()->unique();
            $table->string('vehicle_name');
            $table->string('driver_name');
            $table->string('contact_no');
            $table->string('license_no');
            $table->date('license_expiry_date');
            $table->string('vehicle_registration_no');
            $table->string('vehicle_no');
            $table->decimal('cost', 8, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transports');
    }
};

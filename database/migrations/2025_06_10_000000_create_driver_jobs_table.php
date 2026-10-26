<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Placeholder restored from empty migration file.
 * Job sheets currently do not use a driver_jobs Eloquent table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_jobs')) {
            return;
        }

        Schema::create('driver_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->string('status')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_jobs');
    }
};

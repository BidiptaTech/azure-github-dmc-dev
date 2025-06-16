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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->uuid('unique_tour_id')->default(DB::raw('gen_random_uuid()'))->unique();
            $table->string('destination');
            $table->integer('adult');
            $table->integer('child');
            $table->datetime('check_in_time');
            $table->datetime('check_out_time');
            $table->timestamps();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour');
    }
};

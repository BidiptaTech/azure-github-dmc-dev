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
        Schema::create('multi_restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('package_id')->unique()->comment('Unique package identifier');
            $table->string('package_name');
            $table->json('restaurants')->nullable()->comment('JSON array of restaurant ids or restaurant data');
            $table->integer('price')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multi_restaurants');
    }
};

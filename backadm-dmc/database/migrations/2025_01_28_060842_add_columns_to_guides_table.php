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
        Schema::table('guides', function (Blueprint $table) {

            $table->string('government_license_no')->nullable();
            $table->string('license_image')->nullable();
            $table->date('license_exp_date')->nullable();

            // Adding certification and experience details
            $table->integer('certified')->nullable();
            $table->integer('experience_years')->nullable();

            $table->json('language_proficiency')->nullable();

            $table->decimal('day_rate', 10, 2)->nullable();
            $table->decimal('night_surcharge', 10, 2)->nullable();
            $table->time('night_start_time')->nullable();
            $table->time('night_end_time')->nullable();

            $table->decimal('hourly_price', 10, 2)->nullable();
            $table->decimal('two_hour_price', 10, 2)->nullable();
            $table->decimal('four_hour_price', 10, 2)->nullable();
            $table->decimal('six_hour_price', 10, 2)->nullable();
            $table->decimal('eight_hour_price', 10, 2)->nullable();
            $table->decimal('ten_hour_price', 10, 2)->nullable();
            $table->decimal('twelve_hour_price', 10, 2)->nullable();
            $table->decimal('rating')->nullable();
            $table->integer('service_type')->default(1)->comment('1 = private, 2 = shared');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            //
            $table->dropColumn([
                'government_license',
                'license_image',
                'license_expiry_date',
                'certified_batch',
                'years_of_experience',
                'language_proficiency',
                'day_rate',
                'night_surcharge',
                'night_time_range_start',
                'night_time_range_end',
                'hourly_price',
                'two_hour_price',
                'four_hour_price',
                'six_hour_price',
                'eight_hour_price',
                'ten_hour_price',
                'twelve_hour_price',
                'rating',
                'service_type'
            ]);
        });
    }
};

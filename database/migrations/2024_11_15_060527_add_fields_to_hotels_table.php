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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('management_comp_name')->nullable(); 
            $table->string('hotel_reservation_cont_no')->nullable(); 
            $table->string('hotel_reservation_email')->nullable(); 
            $table->string('revenue_director_cont_no')->nullable(); 
            $table->string('revenue_director_email')->nullable(); 
            $table->string('sales_director_cont_no')->nullable(); 
            $table->string('sales_director_email')->nullable(); 
            $table->string('finance_director_cont_no')->nullable(); 
            $table->string('finance_director_email')->nullable(); 
            $table->string('food&beverage_director_cont_no')->nullable(); 
            $table->string('food&beverage_director_email')->nullable(); 
            $table->string('marketing_manager_cont_no')->nullable(); 
            $table->string('marketing_manager_email')->nullable(); 
            $table->string('account_manager_cont_no')->nullable(); 
            $table->string('account_manager_email')->nullable(); 
            $table->string('general_manager_cont_no')->nullable(); 
            $table->string('general_manager_email')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            //
        });
    }
};

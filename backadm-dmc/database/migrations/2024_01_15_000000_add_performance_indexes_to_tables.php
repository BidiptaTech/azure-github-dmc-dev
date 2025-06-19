<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add indexes for frequently queried columns to improve API performance
        
        // Countries table - for case-sensitive name lookups
        Schema::table('countries', function (Blueprint $table) {
            $table->index(['name'], 'idx_countries_name');
        });
        
        // Hotels table - for location and status filtering
        Schema::table('hotels', function (Blueprint $table) {
            $table->index(['city', 'status', 'is_active', 'is_complete'], 'idx_hotels_location_status');
            $table->index(['dmc_id', 'city', 'country'], 'idx_hotels_dmc_location');
            $table->index(['address'], 'idx_hotels_address');
        });
        
        // Vehicles table - for driver and location filtering
        Schema::table('vehicles', function (Blueprint $table) {
            $table->index(['dmc_id', 'city'], 'idx_vehicles_dmc_city');
            $table->index(['driver_id'], 'idx_vehicles_driver');
        });
        
        // Attractions table - for location filtering
        Schema::table('attractions', function (Blueprint $table) {
            $table->index(['dmc_id', 'location', 'country'], 'idx_attractions_dmc_location');
        });
        
        // Restaurants table - for location filtering
        Schema::table('restaurants', function (Blueprint $table) {
            $table->index(['dmc_id', 'city', 'country'], 'idx_restaurants_dmc_location');
        });
        
        // Guides table - for location filtering
        Schema::table('guides', function (Blueprint $table) {
            $table->index(['dmc_id', 'city', 'country'], 'idx_guides_dmc_location');
        });
        
        // Operational Countries table - for city lookups
        Schema::table('operational_countries', function (Blueprint $table) {
            $table->index(['name', 'city'], 'idx_operational_countries_name_city');
        });
        
        // Users table - for role and DMC filtering
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role_id', 'country'], 'idx_users_role_country');
            $table->index(['userId'], 'idx_users_userid');
        });
        
        // Ports table - for city filtering
        Schema::table('ports', function (Blueprint $table) {
            $table->index(['city_id', 'country'], 'idx_ports_city_country');
        });
        
        // Orders table - for tour filtering
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['tour_id'], 'idx_orders_tour_id');
            $table->index(['type'], 'idx_orders_type');
        });
        
        // Tours table - for agent filtering
        Schema::table('tours', function (Blueprint $table) {
            $table->index(['agent_id'], 'idx_tours_agent_id');
        });
        
        // Enquiry Forms table - for agent and tour filtering
        Schema::table('enquiry_forms', function (Blueprint $table) {
            $table->index(['agent_id', 'unique_tour_id'], 'idx_enquiry_forms_agent_tour');
        });
        
        // Agents table - for sales manager filtering
        Schema::table('agents', function (Blueprint $table) {
            $table->index(['sales_manager_dmc'], 'idx_agents_sales_manager');
        });
        
        // Meals table - for restaurant filtering
        Schema::table('meals', function (Blueprint $table) {
            $table->index(['restaurant_id'], 'idx_meals_restaurant_id');
        });
        
        // Tickets table - for attraction filtering
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['attraction_id'], 'idx_tickets_attraction_id');
        });
        
        // Beds table - for room filtering
        Schema::table('beds', function (Blueprint $table) {
            $table->index(['room_id'], 'idx_beds_room_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes in reverse order
        Schema::table('beds', function (Blueprint $table) {
            $table->dropIndex('idx_beds_room_id');
        });
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_attraction_id');
        });
        
        Schema::table('meals', function (Blueprint $table) {
            $table->dropIndex('idx_meals_restaurant_id');
        });
        
        Schema::table('agents', function (Blueprint $table) {
            $table->dropIndex('idx_agents_sales_manager');
        });
        
        Schema::table('enquiry_forms', function (Blueprint $table) {
            $table->dropIndex('idx_enquiry_forms_agent_tour');
        });
        
        Schema::table('tours', function (Blueprint $table) {
            $table->dropIndex('idx_tours_agent_id');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_tour_id');
            $table->dropIndex('idx_orders_type');
        });
        
        Schema::table('ports', function (Blueprint $table) {
            $table->dropIndex('idx_ports_city_country');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_country');
            $table->dropIndex('idx_users_userid');
        });
        
        Schema::table('operational_countries', function (Blueprint $table) {
            $table->dropIndex('idx_operational_countries_name_city');
        });
        
        Schema::table('guides', function (Blueprint $table) {
            $table->dropIndex('idx_guides_dmc_location');
        });
        
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropIndex('idx_restaurants_dmc_location');
        });
        
        Schema::table('attractions', function (Blueprint $table) {
            $table->dropIndex('idx_attractions_dmc_location');
        });
        
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('idx_vehicles_dmc_city');
            $table->dropIndex('idx_vehicles_driver');
        });
        
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropIndex('idx_hotels_location_status');
            $table->dropIndex('idx_hotels_dmc_location');
            $table->dropIndex('idx_hotels_address');
        });
        
        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex('idx_countries_name');
        });
    }
}; 
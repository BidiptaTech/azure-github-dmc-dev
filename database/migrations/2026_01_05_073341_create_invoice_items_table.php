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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id'); // Foreign key to invoices table (invoice_id, not id)
            $table->string('item_type')->nullable(); // hotel, transfer, attraction, meal, etc.
            $table->string('description')->nullable(); // Service description
            
            // Service Details (JSON for flexibility - different structures for different service types)
            $table->json('service_details')->nullable(); // Room category, check-in, check-out, days, confirmation, transfer type, vehicle, ticket details, etc.
            
            // Quantities
            $table->integer('quantity_adults')->default(0);
            $table->integer('quantity_children')->default(0);
            $table->integer('quantity_infants')->default(0);
            
            // Pricing
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            
            // Display order
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            
            // Indexes
            $table->index('invoice_id');
            $table->index('item_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};

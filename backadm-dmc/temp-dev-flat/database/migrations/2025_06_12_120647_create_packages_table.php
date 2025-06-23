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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('title');
            $table->string('destination');
            $table->string('category');
            $table->integer('duration_days');
            $table->text('description')->nullable();
            
            // Pricing & Capacity
            $table->decimal('price_adult', 10, 2);
            $table->decimal('price_senior', 10, 2)->nullable();
            $table->decimal('price_child', 10, 2)->nullable();
            $table->integer('max_pax');
            
            // Hotels & Attractions Selection
            $table->json('selected_hotels')->nullable();
            $table->json('selected_attractions')->nullable();
            $table->integer('max_hotels')->nullable();
            $table->integer('max_attractions')->nullable();
            
            // Package Images
            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();
            
            // Available Dates
            $table->json('available_dates')->nullable();
            
            // Inclusions & Exclusions
            $table->text('inclusions')->nullable();
            $table->text('exclusions')->nullable();
            
            // Terms & Conditions
            $table->text('terms_conditions')->nullable();
            
            // Status & Meta
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            
            // User tracking
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['destination', 'status']);
            $table->index(['category', 'status']);
            $table->index(['status', 'is_featured']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

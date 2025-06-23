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
            $table->string('title');
            $table->string('destination');
            $table->string('city');
            $table->string('category');
            $table->integer('duration_days');
            $table->text('description')->nullable();
            $table->decimal('price_adult', 10, 2);
            $table->decimal('price_senior', 10, 2)->nullable();
            $table->decimal('price_child', 10, 2)->nullable();
            $table->integer('max_pax')->nullable();
            $table->json('selected_hotels')->nullable();
            $table->json('selected_attractions')->nullable();
            $table->json('selected_guide')->nullable();
            $table->json('selected_restaurants')->nullable();
            $table->integer('max_hotels')->nullable();
            $table->integer('max_attractions')->nullable();
            $table->integer('max_restaurants')->nullable();
            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->text('inclusions')->nullable();
            $table->text('exclusions')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
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

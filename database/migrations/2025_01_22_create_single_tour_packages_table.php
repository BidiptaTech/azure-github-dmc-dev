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
        Schema::create('single_tour_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dmc_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('city_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            $table->bigInteger('agent_id');
            $table->string('package_name');
            $table->decimal('estimated_budget', 10, 2)->nullable();
            $table->text('package_description')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->enum('status', ['draft', 'pending', 'confirmed', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('dmc_id')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('agent_id')->references('agent_id')->on('agents')->onDelete('cascade');
            $table->foreign('created_by')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('userId')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('single_tour_packages');
    }
}; 
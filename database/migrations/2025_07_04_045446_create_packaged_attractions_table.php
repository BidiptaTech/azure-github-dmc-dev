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
        Schema::create('packaged_attractions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_attraction_id');
            $table->string('name')->nullable();
            $table->json('attractions')->nullable();
            $table->decimal('senior_citizen_price', 10, 2)->nullable();
            $table->decimal('adult_price', 10, 2)->nullable();
            $table->decimal('child_price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->json('image')->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('dmc_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packaged_attractions');
    }
};

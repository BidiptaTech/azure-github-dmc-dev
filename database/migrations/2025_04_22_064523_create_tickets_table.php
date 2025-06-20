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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ticket_id')->unique();
            $table->string('name');
            $table->decimal('child_price')->nullable();
            $table->decimal('adult_price')->nullable();
            $table->decimal('senior_adult_price')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: active, 0: inactive');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

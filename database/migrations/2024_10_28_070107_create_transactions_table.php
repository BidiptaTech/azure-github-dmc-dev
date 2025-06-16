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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->integer('credited_from'); 
            $table->enum('type', ['commission', 'transaction'])->comment('Transaction types: commission, transaction'); 
            $table->decimal('amount', 10, 2); // Specify precision and scale for decimal
            $table->timestamps();
        
            // Foreign key constraint
            $table->foreign('user_id')->references('userId')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

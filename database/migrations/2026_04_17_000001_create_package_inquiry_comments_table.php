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
        Schema::create('package_inquiry_comments', function (Blueprint $table) {
            $table->id();

            // Mirror enquiry_comments style: a generated unique enquiry_id (not auto-increment)
            $table->bigInteger('enquiry_id')->unique();

            // booking_id is string like PB00239
            $table->string('booking_id', 50);
            $table->unsignedBigInteger('package_id')->nullable();

            // optional scoping
            $table->unsignedBigInteger('dmc_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();

            // sender/receiver flow (same style as enquiry_comments)
            $table->integer('sender_id')->nullable();
            $table->string('sender_type')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->string('receiver_type')->nullable();
            $table->string('current_position')->nullable();

            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->text('comment')->nullable();
            $table->integer('status')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'package_id']);
            $table->index(['dmc_id']);
            $table->index(['agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_inquiry_comments');
    }
};


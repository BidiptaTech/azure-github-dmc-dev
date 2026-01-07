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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('credit_note_id')->unique(); // Using CommonHelper::createId
            $table->integer('invoice_id'); // Foreign key to invoices table (final invoice)
            $table->integer('tour_id'); // Reference to tour
            $table->integer('dmc_id');
            
            // Credit Note Number
            $table->string('credit_note_number')->unique();
            
            // Dates
            $table->date('credit_note_date');
            
            // Reason for credit note
            $table->enum('reason', ['cancellation', 'refund', 'adjustment', 'other'])->default('cancellation');
            $table->text('reason_description')->nullable();
            
            // Financial Information
            $table->string('currency', 3)->default('SGD');
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            
            // Refund Information
            $table->enum('refund_status', ['pending', 'processed', 'completed'])->default('pending');
            $table->date('refund_date')->nullable();
            $table->text('refund_details')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'issued', 'applied'])->default('draft');
            
            // Additional Fields
            $table->text('notes')->nullable();
            
            // Audit Fields
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes(); // deleted_at
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_id');
            $table->index('tour_id');
            $table->index('credit_note_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};

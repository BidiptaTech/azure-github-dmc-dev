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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id')->unique(); // Using CommonHelper::createId
            $table->integer('tour_id'); // Foreign key to tours table
            $table->integer('dmc_id'); // DMC who issued the invoice
            $table->integer('agent_id')->nullable(); // Agent/Travel Company
            
            // Invoice Type & Status
            $table->enum('invoice_type', ['proforma', 'final'])->default('proforma');
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('draft');
            
            // Invoice Numbering
            $table->string('invoice_number')->unique()->nullable(); // Only for final invoices
            $table->string('proforma_number')->nullable(); // For proforma invoices
            
            // Dates
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('validity_date')->nullable(); // For proforma invoices
            
            // Client/Guest Information (JSON for flexibility)
            $table->json('client_details')->nullable(); // Address, city, country, email, phone, booking_id, lead_guest_name
            $table->json('travel_company_details')->nullable(); // Name, address, contact person, phone, email
            
            // Travel Details
            $table->string('destination')->nullable();
            $table->date('travel_from_date')->nullable();
            $table->date('travel_to_date')->nullable();
            $table->integer('duration_days')->nullable();
            $table->integer('no_of_adults')->default(0);
            $table->integer('no_of_children')->default(0);
            $table->integer('no_of_infants')->default(0);
            
            // Financial Information
            $table->string('base_currency', 3)->default('SGD'); // Base currency (destination's local currency)
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0); // Only for final invoices
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->decimal('tourist_tax', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('payment_received', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            
            // Currency Conversion (JSON for multiple currencies)
            $table->json('currency_conversion')->nullable(); // USD, SGD, INR amounts
            
            // Payment Terms (JSON)
            $table->json('payment_terms')->nullable();
            
            // Bank Details (JSON)
            $table->json('bank_details')->nullable();
            
            // Additional Fields
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->string('sent_by')->nullable(); // User who sent the invoice
            
            // Audit Fields
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes(); // deleted_at for soft deletes
            $table->timestamps();
            
            // Indexes
            $table->index('tour_id');
            $table->index('dmc_id');
            $table->index('invoice_type');
            $table->index('status');
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

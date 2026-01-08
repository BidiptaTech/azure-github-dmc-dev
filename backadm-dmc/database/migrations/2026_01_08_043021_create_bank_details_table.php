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
        Schema::create('bank_details', function (Blueprint $table) {
            $table->id();
            $table->integer('bank_detail_id')->unique(); // Using CommonHelper::createId
            $table->integer('dmc_id')->nullable(); // DMC who created this bank detail
            
            // Terms & Conditions
            $table->text('terms_and_conditions')->nullable();
            
            // Payment Terms (JSON array)
            $table->json('payment_terms')->nullable();
            
            // Required Bank Fields
            $table->string('account_name');
            $table->string('account_number');
            $table->text('bank_address');
            
            // Optional Bank Fields
            $table->string('ifsc')->nullable(); // For India only
            $table->string('swift_bic_iban')->nullable(); // For international, Europe transfers
            $table->string('bank_code')->nullable(); // For Singapore
            $table->string('branch_code')->nullable(); // For Singapore
            $table->string('aba_routing')->nullable(); // For USA only
            
            // Status
            $table->boolean('is_active')->default(1);
            
            // Audit Fields
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes(); // deleted_at for soft deletes
            $table->timestamps();
            
            // Indexes
            $table->index('dmc_id');
            $table->index('bank_detail_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_details');
    }
};

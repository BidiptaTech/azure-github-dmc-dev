<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiry_comments', function (Blueprint $table) {
            // Gross tour amount captured at the moment a negotiation is submitted. Used to detect
            // services added after a negotiation so their price can be added on top of the agreed amount.
            if (!Schema::hasColumn('enquiry_comments', 'gross_amount')) {
                $table->decimal('gross_amount', 15, 2)->nullable()->after('actual_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_comments', function (Blueprint $table) {
            if (Schema::hasColumn('enquiry_comments', 'gross_amount')) {
                $table->dropColumn('gross_amount');
            }
        });
    }
};

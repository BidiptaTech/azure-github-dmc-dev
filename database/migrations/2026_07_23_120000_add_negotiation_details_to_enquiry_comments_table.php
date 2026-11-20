<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiry_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('enquiry_comments', 'negotiation_details')) {
                $table->json('negotiation_details')->nullable()->after('gross_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_comments', function (Blueprint $table) {
            if (Schema::hasColumn('enquiry_comments', 'negotiation_details')) {
                $table->dropColumn('negotiation_details');
            }
        });
    }
};

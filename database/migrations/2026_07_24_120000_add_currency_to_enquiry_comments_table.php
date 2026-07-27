<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiry_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('enquiry_comments', 'currency')) {
                $table->string('currency', 10)->nullable()->after('negotiation_details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_comments', function (Blueprint $table) {
            if (Schema::hasColumn('enquiry_comments', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};

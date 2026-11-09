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
        Schema::table('external_api_receives', function (Blueprint $table) {
            $table->boolean('dmc_email')->default(false)->after('payload');
            $table->string('email_sent')->nullable()->after('dmc_email');
            $table->unsignedBigInteger('tour_id')->nullable()->after('email_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_api_receives', function (Blueprint $table) {
            $table->dropColumn(['dmc_email', 'email_sent', 'tour_id']);
        });
    }
};

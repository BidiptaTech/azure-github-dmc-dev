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
        Schema::table('tours', function (Blueprint $table) {
            if (! Schema::hasColumn('tours', 'master_dmc_id')) {
                $table->unsignedBigInteger('master_dmc_id')->nullable()->after('dmc_id');
                $table->index('master_dmc_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'master_dmc_id')) {
                $table->dropIndex(['master_dmc_id']);
                $table->dropColumn('master_dmc_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (! Schema::hasColumn('countries', 'current_rate')) {
                // 1 SGD expressed in this country's currency.
                $table->decimal('current_rate', 18, 8)->nullable();
            }
            if (! Schema::hasColumn('countries', 'rate_updated_at')) {
                $table->timestamp('rate_updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['current_rate', 'rate_updated_at']);
        });
    }
};

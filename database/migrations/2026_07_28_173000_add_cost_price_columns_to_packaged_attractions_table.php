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
        Schema::table('packaged_attractions', function (Blueprint $table) {
            if (!Schema::hasColumn('packaged_attractions', 'senior_citizen_cost_price')) {
                $table->decimal('senior_citizen_cost_price', 10, 2)->nullable()->after('senior_citizen_price');
            }
            if (!Schema::hasColumn('packaged_attractions', 'adult_cost_price')) {
                $table->decimal('adult_cost_price', 10, 2)->nullable()->after('adult_price');
            }
            if (!Schema::hasColumn('packaged_attractions', 'child_cost_price')) {
                $table->decimal('child_cost_price', 10, 2)->nullable()->after('child_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packaged_attractions', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('packaged_attractions', 'senior_citizen_cost_price')) {
                $columns[] = 'senior_citizen_cost_price';
            }
            if (Schema::hasColumn('packaged_attractions', 'adult_cost_price')) {
                $columns[] = 'adult_cost_price';
            }
            if (Schema::hasColumn('packaged_attractions', 'child_cost_price')) {
                $columns[] = 'child_cost_price';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

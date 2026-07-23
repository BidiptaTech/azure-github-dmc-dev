<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_zone_mappings', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_zone_mappings', 'private_profit_type')) {
                $table->string('private_profit_type', 20)->nullable()->default('percentage')->after('private_cost_price');
            }
            if (!Schema::hasColumn('vehicle_zone_mappings', 'private_profit_amount')) {
                $table->decimal('private_profit_amount', 50, 2)->default(0)->after('private_profit_type');
            }
            if (!Schema::hasColumn('vehicle_zone_mappings', 'shared_profit_type')) {
                $table->string('shared_profit_type', 20)->nullable()->default('percentage')->after('shared_cost_price');
            }
            if (!Schema::hasColumn('vehicle_zone_mappings', 'shared_profit_amount')) {
                $table->decimal('shared_profit_amount', 50, 2)->default(0)->after('shared_profit_type');
            }
        });

        // Ensure Numeric(50,2) even if columns were previously created as decimal(10,2)
        if (Schema::hasColumn('vehicle_zone_mappings', 'private_profit_amount')) {
            DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN private_profit_amount TYPE NUMERIC(50,2) USING COALESCE(private_profit_amount, 0)::numeric(50,2)');
        }
        if (Schema::hasColumn('vehicle_zone_mappings', 'shared_profit_amount')) {
            DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN shared_profit_amount TYPE NUMERIC(50,2) USING COALESCE(shared_profit_amount, 0)::numeric(50,2)');
        }
    }

    public function down(): void
    {
        Schema::table('vehicle_zone_mappings', function (Blueprint $table) {
            foreach (['private_profit_type', 'private_profit_amount', 'shared_profit_type', 'shared_profit_amount'] as $col) {
                if (Schema::hasColumn('vehicle_zone_mappings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

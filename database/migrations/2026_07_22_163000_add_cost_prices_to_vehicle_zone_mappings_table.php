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
            if (!Schema::hasColumn('vehicle_zone_mappings', 'private_cost_price')) {
                $table->decimal('private_cost_price', 50, 2)->default(0)->after('private_price');
            }
            if (!Schema::hasColumn('vehicle_zone_mappings', 'shared_cost_price')) {
                $table->decimal('shared_cost_price', 50, 2)->default(0)->after('shared_price');
            }
        });

        // Ensure Numeric(50,2) even if columns were previously created as decimal(10,2)
        DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN private_cost_price TYPE NUMERIC(50,2) USING COALESCE(private_cost_price, 0)::numeric(50,2)');
        DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN shared_cost_price TYPE NUMERIC(50,2) USING COALESCE(shared_cost_price, 0)::numeric(50,2)');

        // Default existing cost prices to current sell prices (cast for varchar sell columns)
        DB::statement("UPDATE vehicle_zone_mappings SET private_cost_price = COALESCE(NULLIF(TRIM(private_price::text), '')::numeric, 0), shared_cost_price = COALESCE(NULLIF(TRIM(shared_price::text), '')::numeric, 0)");
    }

    public function down(): void
    {
        Schema::table('vehicle_zone_mappings', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_zone_mappings', 'private_cost_price')) {
                $table->dropColumn('private_cost_price');
            }
            if (Schema::hasColumn('vehicle_zone_mappings', 'shared_cost_price')) {
                $table->dropColumn('shared_cost_price');
            }
        });
    }
};

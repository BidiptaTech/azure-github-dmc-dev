<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert sell price columns to NUMERIC(50,2).
     */
    public function up(): void
    {
        if (Schema::hasColumn('vehicle_zone_mappings', 'private_price')) {
            DB::statement("ALTER TABLE vehicle_zone_mappings ALTER COLUMN private_price TYPE NUMERIC(50,2) USING COALESCE(NULLIF(TRIM(private_price::text), '')::numeric, 0)::numeric(50,2)");
            DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN private_price SET DEFAULT 0');
        }

        if (Schema::hasColumn('vehicle_zone_mappings', 'shared_price')) {
            DB::statement("ALTER TABLE vehicle_zone_mappings ALTER COLUMN shared_price TYPE NUMERIC(50,2) USING COALESCE(NULLIF(TRIM(shared_price::text), '')::numeric, 0)::numeric(50,2)");
            DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN shared_price SET DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_zone_mappings', 'private_price')) {
            DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN private_price TYPE VARCHAR(191) USING private_price::text');
        }

        if (Schema::hasColumn('vehicle_zone_mappings', 'shared_price')) {
            DB::statement('ALTER TABLE vehicle_zone_mappings ALTER COLUMN shared_price TYPE VARCHAR(191) USING shared_price::text');
        }
    }
};

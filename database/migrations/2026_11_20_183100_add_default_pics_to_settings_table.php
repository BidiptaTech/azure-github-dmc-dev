<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Seed master-setting keys for default profile pictures.
     * Settings are stored as name/value rows (same pattern as logo/favicon).
     */
    public function up(): void
    {
        $now = Carbon::now();
        $keys = [
            'guide_default_pic',
            'vehicle_default_pic',
            'driver_default_pic',
        ];

        foreach ($keys as $name) {
            $exists = DB::table('settings')->where('name', $name)->exists();
            if ($exists) {
                continue;
            }

            DB::table('settings')->insert([
                'name' => $name,
                'value' => '',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('name', [
            'guide_default_pic',
            'vehicle_default_pic',
            'driver_default_pic',
        ])->delete();
    }
};

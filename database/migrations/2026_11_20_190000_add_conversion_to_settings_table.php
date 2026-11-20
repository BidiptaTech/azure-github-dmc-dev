<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Seed master-setting key for conversion (numeric value stored as settings.name/value).
     */
    public function up(): void
    {
        $exists = DB::table('settings')->where('name', 'conversion')->exists();
        if ($exists) {
            return;
        }

        $now = Carbon::now();
        DB::table('settings')->insert([
            'name' => 'conversion',
            'value' => '0',
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('name', 'conversion')->delete();
    }
};

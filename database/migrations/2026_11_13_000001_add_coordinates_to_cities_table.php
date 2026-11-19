<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hotelbeds (and other geo suppliers) need per-city coordinates.
     * Without these, Hotelbeds was hardcoded to Singapore and Batam searches
     * returned Singapore hotels.
     *
     * @var array<string, array{0: float, 1: float}>
     */
    private const CITY_COORDINATES = [
        'singapore' => [1.290270, 103.851959],
        'batam' => [1.130000, 104.053000],
        'bintan' => [1.100000, 104.450000],
        'jakarta' => [-6.208800, 106.845600],
        'bali' => [-8.409500, 115.188900],
        'denpasar' => [-8.670500, 115.212600],
        'bandung' => [-6.917500, 107.619100],
        'surabaya' => [-7.257500, 112.752100],
        'medan' => [3.595200, 98.672200],
        'yogyakarta' => [-7.795600, 110.369500],
        'semarang' => [-6.966700, 110.416700],
        'makassar' => [-5.147700, 119.432700],
        'malang' => [-7.966600, 112.632600],
        'palembang' => [-2.976100, 104.775400],
        'pekanbaru' => [0.507100, 101.447800],
        'padang' => [-0.947100, 100.417200],
        'banjarmasin' => [-3.318600, 114.594400],
        'samarinda' => [-0.502200, 117.153600],
        'bogor' => [-6.597100, 106.806000],
        'bekasi' => [-6.238300, 106.975600],
        'depok' => [-6.402500, 106.794200],
        'tangerang' => [-6.178300, 106.631900],
        'tasikmalaya' => [-7.327400, 108.220700],
    ];

    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('mg_city_code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        foreach (self::CITY_COORDINATES as $name => [$lat, $lng]) {
            DB::table('cities')
                ->whereRaw('LOWER(name) = ?', [$name])
                ->whereNull('deleted_at')
                ->update([
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};

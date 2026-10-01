<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beds_master', function (Blueprint $table) {
            if (!Schema::hasColumn('beds_master', 'child_wo_bed')) {
                if (Schema::hasColumn('beds_master', 'no_of_bunk_bed')) {
                    $table->unsignedTinyInteger('child_wo_bed')->default(0)->after('no_of_bunk_bed');
                } else {
                    $table->unsignedTinyInteger('child_wo_bed')->default(0);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('beds_master', function (Blueprint $table) {
            if (Schema::hasColumn('beds_master', 'child_wo_bed')) {
                $table->dropColumn('child_wo_bed');
            }
        });
    }
};

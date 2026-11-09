<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lost_found')) {
            return;
        }

        Schema::table('lost_found', function (Blueprint $table) {
            if (!Schema::hasColumn('lost_found', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('lost_found', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lost_found')) {
            return;
        }

        Schema::table('lost_found', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('lost_found', 'created_at')) {
                $columns[] = 'created_at';
            }
            if (Schema::hasColumn('lost_found', 'updated_at')) {
                $columns[] = 'updated_at';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

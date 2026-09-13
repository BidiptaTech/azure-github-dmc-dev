<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'markup_type_attraction')) {
                $table->unsignedTinyInteger('markup_type_attraction')
                    ->default(1)
                    ->comment('0=flat, 1=percentage')
                    ->after('markup_price');
            }
            if (! Schema::hasColumn('users', 'markup_price_attraction')) {
                $table->decimal('markup_price_attraction', 12, 2)
                    ->default(0)
                    ->after('markup_type_attraction');
            }
            if (! Schema::hasColumn('users', 'markup_type_flight')) {
                $table->unsignedTinyInteger('markup_type_flight')
                    ->default(1)
                    ->comment('0=flat, 1=percentage')
                    ->after('markup_price_attraction');
            }
            if (! Schema::hasColumn('users', 'markup_price_flight')) {
                $table->decimal('markup_price_flight', 12, 2)
                    ->default(0)
                    ->after('markup_type_flight');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'markup_price_flight',
                'markup_type_flight',
                'markup_price_attraction',
                'markup_type_attraction',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dmc_stripe_accounts')) {
            return;
        }

        Schema::table('dmc_stripe_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('dmc_stripe_accounts', 'country_code')) {
                $table->string('country_code', 2)->nullable()->after('stripe_mode');
            }
            if (! Schema::hasColumn('dmc_stripe_accounts', 'default_currency')) {
                $table->string('default_currency', 3)->nullable()->after('country_code');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('dmc_stripe_accounts')) {
            return;
        }

        Schema::table('dmc_stripe_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('dmc_stripe_accounts', 'default_currency')) {
                $table->dropColumn('default_currency');
            }
            if (Schema::hasColumn('dmc_stripe_accounts', 'country_code')) {
                $table->dropColumn('country_code');
            }
        });
    }
};

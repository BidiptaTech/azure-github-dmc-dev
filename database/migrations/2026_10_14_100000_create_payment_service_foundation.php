<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backward-compatible payment-service foundation.
 * Additive only — does not alter historical wallet/payment balances.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tours')) {
            Schema::table('tours', function (Blueprint $table) {
                if (! Schema::hasColumn('tours', 'order_classification')) {
                    $table->string('order_classification', 20)->nullable()->after('finance_status');
                }
                if (! Schema::hasColumn('tours', 'payment_stage')) {
                    $table->string('payment_stage', 32)->nullable()->default('none')->after('order_classification');
                }
            });
        }

        if (! Schema::hasTable('payment_platform_settings')) {
            Schema::create('payment_platform_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('offline_enabled')->default(true);
                $table->boolean('wallet_enabled')->default(true);
                $table->boolean('online_enabled')->default(false);
                $table->string('stripe_mode', 10)->default('test'); // test|live
                $table->string('updated_by')->nullable();
                $table->json('audit_log')->nullable();
                $table->timestamps();
            });

            DB::table('payment_platform_settings')->insert([
                'offline_enabled' => true,
                'wallet_enabled' => (bool) env('PAYMENT_WALLET_ENABLED', true),
                'online_enabled' => (bool) env('PAYMENT_ONLINE_ENABLED', false),
                'stripe_mode' => env('STRIPE_MODE', 'test'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('dmc_stripe_accounts')) {
            Schema::create('dmc_stripe_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dmc_id')->unique();
                $table->string('stripe_account_id', 128)->nullable();
                $table->string('stripe_mode', 10)->default('test');
                $table->string('onboarding_status', 32)->default('not_started');
                $table->boolean('charges_enabled')->default(false);
                $table->boolean('payouts_enabled')->default(false);
                $table->boolean('details_submitted')->default(false);
                $table->json('requirements_json')->nullable();
                $table->json('capabilities_json')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['dmc_id', 'stripe_mode']);
            });
        }

        if (! Schema::hasTable('currency_conversion_snapshots')) {
            Schema::create('currency_conversion_snapshots', function (Blueprint $table) {
                $table->id();
                $table->uuid('snapshot_uuid')->unique();
                $table->char('source_currency', 3);
                $table->char('destination_currency', 3);
                $table->decimal('source_amount', 18, 4);
                $table->decimal('destination_amount', 18, 4);
                $table->decimal('exchange_rate', 18, 8);
                $table->string('rate_direction', 32)->default('source_to_destination');
                $table->string('rate_provider', 64)->nullable();
                $table->timestamp('rate_timestamp')->nullable();
                $table->timestamp('conversion_timestamp')->useCurrent();
                $table->unsignedTinyInteger('decimal_precision')->default(4);
                $table->string('rounding_policy', 32)->default('half_up');
                $table->decimal('fee_or_spread', 18, 4)->nullable();
                $table->unsignedBigInteger('tour_id')->nullable()->index();
                $table->unsignedBigInteger('payment_transaction_id')->nullable()->index();
                $table->unsignedBigInteger('wallet_ledger_id')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable();
                $table->unsignedBigInteger('dmc_id')->nullable();
                $table->string('stripe_reference', 128)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payment_attempts')) {
            Schema::create('payment_attempts', function (Blueprint $table) {
                $table->id();
                $table->uuid('attempt_uuid')->unique();
                $table->string('idempotency_key', 128)->unique();
                $table->unsignedBigInteger('tour_id')->index();
                $table->string('order_classification', 20)->nullable();
                $table->string('payment_stage', 32)->nullable();
                $table->string('payment_method', 32); // wallet|stripe|cash|cheque|bank_transfer|card_offline
                $table->string('payment_recipient', 32); // travclicks|dmc
                $table->unsignedBigInteger('dmc_id')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable();
                $table->char('currency_code', 3);
                $table->decimal('requested_amount', 18, 4);
                $table->decimal('authorized_amount', 18, 4)->nullable();
                $table->decimal('captured_amount', 18, 4)->nullable();
                $table->decimal('released_amount', 18, 4)->nullable();
                $table->decimal('refunded_amount', 18, 4)->default(0);
                $table->string('status', 40)->default('created');
                $table->unsignedBigInteger('payment_transaction_id')->nullable();
                $table->unsignedBigInteger('fx_snapshot_id')->nullable();
                $table->string('stripe_payment_intent_id', 128)->nullable();
                $table->string('stripe_account_id', 128)->nullable();
                $table->string('wallet_reservation_ref', 64)->nullable();
                $table->json('eligibility_snapshot')->nullable();
                $table->json('metadata')->nullable();
                $table->text('last_error')->nullable();
                $table->unsignedInteger('retry_count')->default(0);
                $table->timestamps();

                $table->index(['tour_id', 'status']);
                $table->index(['stripe_payment_intent_id']);
            });
        }

        if (! Schema::hasTable('supplier_booking_outcomes')) {
            Schema::create('supplier_booking_outcomes', function (Blueprint $table) {
                $table->id();
                $table->uuid('outcome_uuid')->unique();
                $table->unsignedBigInteger('tour_id')->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('payment_attempt_id')->nullable()->index();
                $table->string('supplier_api_id', 64)->nullable();
                $table->string('supplier_booking_reference', 128)->nullable();
                $table->string('status', 32)->default('pending');
                $table->decimal('quoted_amount', 18, 4)->default(0);
                $table->decimal('booked_amount', 18, 4)->nullable();
                $table->char('currency_code', 3)->nullable();
                $table->json('request_meta')->nullable();
                $table->json('response_meta')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('payment_transactions', 'payment_stage')) {
                    $table->string('payment_stage', 32)->nullable();
                }
                if (! Schema::hasColumn('payment_transactions', 'payment_recipient')) {
                    $table->string('payment_recipient', 32)->nullable();
                }
                if (! Schema::hasColumn('payment_transactions', 'stripe_account_id')) {
                    $table->string('stripe_account_id', 128)->nullable();
                }
                if (! Schema::hasColumn('payment_transactions', 'order_classification')) {
                    $table->string('order_classification', 20)->nullable();
                }
                if (! Schema::hasColumn('payment_transactions', 'fx_snapshot_id')) {
                    $table->unsignedBigInteger('fx_snapshot_id')->nullable();
                }
                if (! Schema::hasColumn('payment_transactions', 'payment_attempt_id')) {
                    $table->unsignedBigInteger('payment_attempt_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('wallet') && ! Schema::hasColumn('wallet', 'source_dmc_id')) {
            Schema::table('wallet', function (Blueprint $table) {
                $table->unsignedBigInteger('source_dmc_id')->nullable()->after('wallet_owner_id');
                $table->index(['wallet_owner_type', 'wallet_owner_id', 'source_dmc_id']);
            });
        }

        // Expand ledger entry types for allocation + reserve lifecycle (PostgreSQL).
        if (Schema::hasTable('wallet_ledger') && DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE wallet_ledger DROP CONSTRAINT IF EXISTS chk_ledger_entry_type');
            DB::statement("
                ALTER TABLE wallet_ledger
                ADD CONSTRAINT chk_ledger_entry_type CHECK (
                    ledger_entry_type IN (
                        'topup', 'booking_debit', 'booking_credit', 'refund_credit', 'refund_debit',
                        'reserve', 'release', 'commit', 'adjustment',
                        'allocation_debit', 'allocation_credit'
                    )
                )
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                foreach (['payment_stage', 'payment_recipient', 'stripe_account_id', 'order_classification', 'fx_snapshot_id', 'payment_attempt_id'] as $col) {
                    if (Schema::hasColumn('payment_transactions', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('supplier_booking_outcomes');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('currency_conversion_snapshots');
        Schema::dropIfExists('dmc_stripe_accounts');
        Schema::dropIfExists('payment_platform_settings');

        if (Schema::hasTable('tours')) {
            Schema::table('tours', function (Blueprint $table) {
                if (Schema::hasColumn('tours', 'payment_stage')) {
                    $table->dropColumn('payment_stage');
                }
                if (Schema::hasColumn('tours', 'order_classification')) {
                    $table->dropColumn('order_classification');
                }
            });
        }

        if (Schema::hasTable('wallet') && Schema::hasColumn('wallet', 'source_dmc_id')) {
            Schema::table('wallet', function (Blueprint $table) {
                $table->dropColumn('source_dmc_id');
            });
        }
    }
};

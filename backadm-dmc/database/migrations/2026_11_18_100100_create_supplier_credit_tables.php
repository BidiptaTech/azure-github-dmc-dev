<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TravClicks ↔ API vendor credit (one account per supplier code + environment).
 *
 * - supplier_credit_accounts: the credit TravClicks holds with each vendor, in the
 *   currency that vendor settles in (USD / SGD / …), separately for demo (sandbox) and live.
 * - supplier_credit_transactions: append-only statement. Every top-up, adjustment,
 *   booking debit and cancellation refund is one row with balance before/after and FX.
 *
 * All money columns are numeric(18,2) (2 decimal places); FX rates keep numeric(18,8).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('supplier_credit_accounts')) {
            Schema::create('supplier_credit_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('supplier_code', 50);
                $table->string('environment', 10); // demo | live
                $table->char('currency_code', 3);
                $table->decimal('total_credit_added', 18, 2)->default(0);
                $table->decimal('balance', 18, 2)->default(0);
                $table->decimal('total_debited', 18, 2)->default(0);
                $table->decimal('total_refunded', 18, 2)->default(0);
                $table->decimal('low_balance_threshold', 18, 2)->nullable();
                $table->boolean('enforce_limit')->default(true);
                $table->string('status', 20)->default('active'); // active | inactive
                $table->boolean('auto_created')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement("ALTER TABLE supplier_credit_accounts ADD CONSTRAINT chk_sca_environment CHECK (environment IN ('demo','live'))");
            DB::statement("ALTER TABLE supplier_credit_accounts ADD CONSTRAINT chk_sca_status CHECK (status IN ('active','inactive'))");
            DB::statement('CREATE UNIQUE INDEX uq_sca_supplier_env ON supplier_credit_accounts (supplier_code, environment) WHERE deleted_at IS NULL');
        }

        if (! Schema::hasTable('supplier_credit_transactions')) {
            Schema::create('supplier_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->uuid('txn_uuid')->unique();
                $table->foreignId('account_id')->constrained('supplier_credit_accounts')->restrictOnDelete();
                $table->string('supplier_code', 50);
                $table->string('environment', 10);
                // topup | adjustment_credit | adjustment_debit | booking_debit | cancel_refund
                $table->string('txn_type', 30);
                $table->string('direction', 6); // debit | credit
                $table->char('currency_code', 3);          // account currency
                $table->decimal('amount', 18, 2);           // always positive, in account currency
                $table->decimal('balance_before', 18, 2);
                $table->decimal('balance_after', 18, 2);
                // What the vendor quoted / charged, before conversion to the account currency
                $table->decimal('supplier_amount', 18, 2)->nullable();
                $table->char('supplier_currency', 3)->nullable();
                $table->decimal('fx_rate', 18, 8)->nullable();
                $table->string('fx_source', 50)->nullable();
                $table->decimal('cancellation_charge', 18, 2)->nullable();
                $table->string('service_type', 20)->nullable(); // hotel | attraction
                $table->unsignedBigInteger('tour_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('related_txn_id')->nullable(); // refund → original debit
                $table->string('supplier_booking_reference', 191)->nullable();
                $table->string('agency_reference', 191)->nullable();
                $table->string('idempotency_key', 191)->unique();
                $table->string('reconciliation_status', 20)->default('unreconciled'); // unreconciled | reconciled | needs_review
                $table->timestamp('reconciled_at')->nullable();
                $table->unsignedBigInteger('reconciled_by')->nullable();
                $table->text('description')->nullable();
                $table->jsonb('metadata')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('created_ip', 45)->nullable();
                $table->timestamps();

                $table->index(['account_id', 'created_at'], 'idx_sct_account_created');
                $table->index(['supplier_code', 'environment', 'created_at'], 'idx_sct_supplier_env_created');
                $table->index('tour_id', 'idx_sct_tour');
                $table->index(['order_id', 'txn_type'], 'idx_sct_order_type');
                $table->index('txn_type', 'idx_sct_type');
            });

            DB::statement("ALTER TABLE supplier_credit_transactions ADD CONSTRAINT chk_sct_direction CHECK (direction IN ('debit','credit'))");
            DB::statement('ALTER TABLE supplier_credit_transactions ADD CONSTRAINT chk_sct_amount_non_negative CHECK (amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_credit_transactions');
        Schema::dropIfExists('supplier_credit_accounts');
    }
};

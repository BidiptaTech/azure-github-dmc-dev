<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            ALTER TABLE payment_transactions
                ADD COLUMN IF NOT EXISTS payment_mode VARCHAR(32);

            UPDATE payment_transactions
            SET payment_mode = CASE
                WHEN payment_mode IS NOT NULL AND payment_mode <> '' THEN payment_mode
                WHEN wallet_id IS NOT NULL THEN 'wallet'
                WHEN payment_gateway_name IS NOT NULL OR gateway_transaction_reference IS NOT NULL THEN 'online_gateway'
                ELSE 'offline_office'
            END
            WHERE payment_mode IS NULL OR payment_mode = '';

            ALTER TABLE payment_transactions
                ALTER COLUMN payment_mode SET DEFAULT 'wallet';

            UPDATE payment_transactions SET payment_mode = 'wallet' WHERE payment_mode IS NULL;

            ALTER TABLE tours
                ALTER COLUMN supplier_api_id TYPE VARCHAR(64) USING supplier_api_id::VARCHAR;

            ALTER TABLE settlements
                ADD COLUMN IF NOT EXISTS settlement_type VARCHAR(32),
                ADD COLUMN IF NOT EXISTS settlement_status VARCHAR(32) NOT NULL DEFAULT 'pending';

            UPDATE settlements
            SET settlement_type = COALESCE(NULLIF(settlement_type, ''), 'wallet_booking'),
                settlement_status = COALESCE(NULLIF(settlement_status, ''), CASE WHEN finance_status = 'settled' THEN 'completed' ELSE 'pending' END);
        ");
    }

    public function down(): void
    {
        // Compatibility migration is intentionally non-reversible.
    }
};

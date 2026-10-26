<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(
            base_path('database/sql/finance/002_alter_tours_finance_columns.sql')
        );

        if ($sql) {
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        $columns = [
            'finance_reference', 'payment_transaction_id', 'settlement_id',
            'payment_mode', 'payment_status', 'gateway_transaction_reference',
            'gateway_response_json', 'supplier_cost', 'travclicks_markup',
            'dmc_commission', 'selling_price', 'supplier_api_id',
            'supplier_booking_reference', 'finance_status', 'payment_received_amount',
            'payment_received_currency', 'payment_gateway_name',
            'payment_gateway_response', 'payment_received_datetime', 'master_dmc_id',
        ];

        foreach ($columns as $column) {
            DB::statement("ALTER TABLE tours DROP COLUMN IF EXISTS {$column}");
        }
    }
};

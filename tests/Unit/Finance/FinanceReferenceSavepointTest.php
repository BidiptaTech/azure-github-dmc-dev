<?php

namespace Tests\Unit\Finance;

use App\Services\Finance\WalletLedgerService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinanceReferenceSavepointTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::rollBack(); // also undoes the sequence rename (Postgres DDL is transactional)
        parent::tearDown();
    }

    public function test_missing_reference_sequence_does_not_abort_the_transaction(): void
    {
        DB::beginTransaction();
        DB::statement('ALTER SEQUENCE finance_reference_seq RENAME TO finance_reference_seq_missing');

        $ref = (new WalletLedgerService)->generateFinanceReference('ALLOC');

        $this->assertStringStartsWith('ALLOC-', $ref);
        // Before the fix this raised SQLSTATE[25P02]: current transaction is aborted.
        $this->assertSame(1, (int) DB::selectOne('SELECT 1 AS ok')->ok);
    }
}

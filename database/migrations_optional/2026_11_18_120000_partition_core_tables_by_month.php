<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Month/year RANGE partitioning on created_at for tours, orders and all payment / ledger tables.
 *
 * PostgreSQL requires every unique key on a partitioned table to include the partition column,
 * and cannot point a foreign key at a partitioned table by `id` alone. To keep today's guarantees:
 *  - each former UNIQUE column is kept globally unique by a registry table + BEFORE trigger
 *    (`partition_unique_keys`), raising the same SQLSTATE 23505 as a unique index;
 *  - each foreign key that pointed INTO a converted table becomes a pair of triggers
 *    (child insert/update check → 23503, parent delete → RESTRICT or CASCADE as before).
 *
 * The original tables are kept as `<table>__prepart` (no FKs) so `down()` can swap back.
 * The whole migration runs in one transaction: any mismatch rolls everything back.
 *
 * OPT-IN (not in database/migrations, so normal deploys never run it). Measured cost at
 * today's volume: ~+280 ms per request on a fresh DB connection and ~6x slower per lookup,
 * because queries filter by tour_id (no partition pruning). Put PgBouncer in front of
 * PostgreSQL before enabling. See docs/ONLINE_SUPPLIER_CREDIT_AND_FX.md.
 *
 *   Apply:    php artisan migrate --path=database/migrations_optional/2026_11_18_120000_partition_core_tables_by_month.php --force
 *   Rollback: php artisan migrate:rollback --path=database/migrations_optional/2026_11_18_120000_partition_core_tables_by_month.php --force
 *   Monthly:  php artisan partitions:ensure   (scheduled; no-op warning until applied)
 *   Cleanup:  php artisan partitions:ensure --drop-legacy   (after verifying; removes <table>__prepart)
 */
return new class extends Migration
{
    /** Order matters only for readability; FKs are dropped up-front and re-added at the end. */
    private const TABLES = [
        'tours',
        'orders',
        'payment_attempts',
        'payment_transactions',
        'settlements',
        'wallet_ledger',
        'order_commission_lines',
        'currency_conversion_snapshots',
        'supplier_booking_outcomes',
        'supplier_credit_transactions',
        'credit_notes',
    ];

    private const MONTHS_AHEAD = 3;

    public function up(): void
    {
        $this->createSupportObjects();

        $targets = array_values(array_filter(self::TABLES, fn ($t) => $this->isPlainTable($t)));
        if ($targets === []) {
            return;
        }

        // 1. Remember + drop every FK touching a target (inbound or outbound).
        $fks = $this->foreignKeysTouching($targets);
        foreach ($fks as $fk) {
            DB::statement(sprintf('ALTER TABLE %s DROP CONSTRAINT %s', $this->q($fk['child']), $this->q($fk['name'])));
        }

        $state = ['fks' => $fks, 'tables' => []];

        // 2. Convert each table.
        foreach ($targets as $table) {
            $state['tables'][$table] = $this->convert($table);
        }

        // 3. Re-create relationships.
        foreach ($fks as $fk) {
            $parentPartitioned = in_array($fk['parent'], $targets, true);
            if (! $parentPartitioned) {
                // Child is partitioned, parent is a plain table → native FK still works.
                DB::statement(sprintf(
                    'ALTER TABLE %s ADD CONSTRAINT %s %s',
                    $this->q($fk['child']),
                    $this->q($fk['name']),
                    str_replace(' NOT VALID', '', $fk['def'])
                ));

                continue;
            }
            $this->createTriggerForeignKey($fk);
        }

        DB::table('partition_conversion_log')->insert([
            'state' => json_encode($state),
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        $row = DB::table('partition_conversion_log')->orderByDesc('id')->first();
        if (! $row) {
            return;
        }
        $state = json_decode($row->state, true);

        // Drop trigger-based FKs first.
        foreach ($state['fks'] as $fk) {
            $base = 'pfk_'.substr(md5($fk['name']), 0, 10);
            DB::statement(sprintf('DROP TRIGGER IF EXISTS %s ON %s', $this->q($base.'_c'), $this->q($fk['child'])));
            DB::statement(sprintf('DROP TRIGGER IF EXISTS %s ON %s', $this->q($base.'_p'), $this->q($fk['parent'])));
            if ($this->relationExists($fk['child'])) {
                DB::statement(sprintf('ALTER TABLE %s DROP CONSTRAINT IF EXISTS %s', $this->q($fk['child']), $this->q($fk['name'])));
            }
        }

        foreach (array_reverse(array_keys($state['tables'])) as $table) {
            $legacy = $table.'__prepart';
            if (! $this->relationExists($legacy)) {
                continue;
            }
            $cols = $this->commonColumns($table, $legacy);
            DB::statement("TRUNCATE {$this->q($legacy)}");
            DB::statement(sprintf('INSERT INTO %s (%s) SELECT %s FROM %s', $this->q($legacy), $cols, $cols, $this->q($table)));
            foreach ($state['tables'][$table]['sequences'] as $seq => $col) {
                DB::statement(sprintf('ALTER SEQUENCE %s OWNED BY %s.%s', $seq, $this->q($legacy), $this->q($col)));
            }
            DB::statement("DROP TABLE {$this->q($table)} CASCADE");
            DB::statement(sprintf('ALTER TABLE %s RENAME TO %s', $this->q($legacy), $this->q($table)));
            foreach ($state['tables'][$table]['index_renames'] as $original => $renamed) {
                DB::statement(sprintf('ALTER INDEX IF EXISTS %s RENAME TO %s', $this->q($renamed), $this->q($original)));
            }
            DB::table('partition_unique_keys')->where('scope', 'like', $table.'.%')->delete();
        }

        foreach ($state['fks'] as $fk) {
            DB::statement(sprintf('ALTER TABLE %s ADD CONSTRAINT %s %s', $this->q($fk['child']), $this->q($fk['name']), $fk['def']));
        }

        DB::table('partition_conversion_log')->where('id', $row->id)->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function convert(string $table): array
    {
        $legacy = $table.'__prepart';
        $oid = $this->oid($table);

        // Unique columns (single or multi), excluding the primary key.
        $uniques = array_map(fn ($r) => $r->cols, DB::select(
            "SELECT string_agg(a.attname, ',' ORDER BY k.ord) AS cols
             FROM pg_index i
             CROSS JOIN LATERAL unnest(i.indkey) WITH ORDINALITY AS k(attnum, ord)
             JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = k.attnum
             WHERE i.indrelid = ? AND i.indisunique AND NOT i.indisprimary AND i.indpred IS NULL
             GROUP BY i.indexrelid",
            [$oid]
        ));
        $uniques = array_values(array_unique($uniques));

        $indexes = DB::select(
            'SELECT c.relname AS name, pg_get_indexdef(i.indexrelid) AS def, i.indisunique AS uniq, i.indisprimary AS pk
             FROM pg_index i JOIN pg_class c ON c.oid = i.indexrelid WHERE i.indrelid = ?',
            [$oid]
        );
        $triggers = DB::select(
            'SELECT tgname AS name, pg_get_triggerdef(oid) AS def FROM pg_trigger WHERE tgrelid = ? AND NOT tgisinternal',
            [$oid]
        );
        $sequences = [];
        foreach (DB::select(
            "SELECT a.attname AS col, pg_get_serial_sequence(?, a.attname) AS seq
             FROM pg_attribute a WHERE a.attrelid = ? AND a.attnum > 0 AND NOT a.attisdropped",
            [$table, $oid]
        ) as $s) {
            if ($s->seq) {
                $sequences[$s->seq] = $s->col;
            }
        }
        $rowCount = (int) DB::selectOne("SELECT count(*) AS c FROM {$this->q($table)}")->c;
        $minMonth = DB::selectOne("SELECT date_trunc('month', min(created_at))::date AS m FROM {$this->q($table)}")->m;

        // Move the original aside (its index names are freed for the new table).
        DB::statement(sprintf('ALTER TABLE %s RENAME TO %s', $this->q($table), $this->q($legacy)));
        $renames = [];
        foreach ($indexes as $ix) {
            $new = substr($ix->name, 0, 50).'_prepart';
            DB::statement(sprintf('ALTER INDEX %s RENAME TO %s', $this->q($ix->name), $this->q($new)));
            $renames[$ix->name] = $new;
        }

        // New partitioned parent with identical columns, defaults and CHECK constraints.
        DB::statement(sprintf(
            'CREATE TABLE %s (LIKE %s INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING GENERATED INCLUDING COMMENTS INCLUDING STORAGE) PARTITION BY RANGE (created_at)',
            $this->q($table),
            $this->q($legacy)
        ));
        DB::statement("ALTER TABLE {$this->q($table)} ALTER COLUMN created_at SET DEFAULT CURRENT_TIMESTAMP");
        DB::statement("ALTER TABLE {$this->q($table)} ALTER COLUMN created_at SET NOT NULL");
        DB::statement(sprintf('ALTER TABLE %s ADD CONSTRAINT %s PRIMARY KEY (id, created_at)', $this->q($table), $this->q($table.'_pkey')));

        // Month partitions from the oldest row through now + MONTHS_AHEAD, plus default.
        $cursor = $minMonth ? CarbonImmutable::parse($minMonth)->startOfMonth() : CarbonImmutable::now()->startOfMonth();
        $end = CarbonImmutable::now()->startOfMonth()->addMonths(self::MONTHS_AHEAD);
        while ($cursor->lte($end)) {
            DB::statement(sprintf(
                "CREATE TABLE %s PARTITION OF %s FOR VALUES FROM ('%s') TO ('%s')",
                $this->q($table.'_p'.$cursor->format('Ym')),
                $this->q($table),
                $cursor->format('Y-m-d'),
                $cursor->addMonth()->format('Y-m-d')
            ));
            $cursor = $cursor->addMonth();
        }
        DB::statement(sprintf('CREATE TABLE %s PARTITION OF %s DEFAULT', $this->q($table.'_pdefault'), $this->q($table)));

        // Copy data and verify.
        DB::statement(sprintf('INSERT INTO %s SELECT * FROM %s', $this->q($table), $this->q($legacy)));
        $copied = (int) DB::selectOne("SELECT count(*) AS c FROM {$this->q($table)}")->c;
        if ($copied !== $rowCount) {
            throw new RuntimeException("Partition copy mismatch for {$table}: {$copied} vs {$rowCount}");
        }

        // Sequences now belong to the new table (dropping the legacy copy must not drop them).
        foreach ($sequences as $seq => $col) {
            DB::statement(sprintf('ALTER SEQUENCE %s OWNED BY %s.%s', $seq, $this->q($table), $this->q($col)));
        }

        // Secondary indexes (unique ones become plain lookups; uniqueness via registry below).
        $seenShapes = [];
        foreach ($indexes as $ix) {
            if ($ix->pk) {
                continue;
            }
            $def = preg_replace('/\bON (ONLY )?(public\.)?"?'.preg_quote($table, '/').'"?\b/', 'ON '.$table, (string) $ix->def, 1);
            $def = preg_replace('/^CREATE UNIQUE INDEX/', 'CREATE INDEX', $def);
            $shape = preg_replace('/^CREATE INDEX \S+/', '', $def);
            if (isset($seenShapes[$shape])) {
                continue; // identical duplicate (e.g. tour_id unique declared twice)
            }
            $seenShapes[$shape] = true;
            $def = preg_replace('/^CREATE INDEX \S+/', 'CREATE INDEX IF NOT EXISTS '.$this->q($ix->name), $def);
            // pg_get_indexdef was captured before the rename, so it names the original table.
            DB::statement($def);
        }

        // Triggers (e.g. updated_at maintenance).
        foreach ($triggers as $tg) {
            $def = preg_replace('/\bON (public\.)?"?'.preg_quote($table, '/').'"?\b/', 'ON '.$table, (string) $tg->def, 1);
            DB::statement($def);
        }

        // Global uniqueness: seed registry from existing rows, then guard every write.
        if ($uniques !== []) {
            $args = [];
            foreach ($uniques as $cols) {
                $scope = $table.'.'.$cols;
                $expr = implode(" || '|' || ", array_map(fn ($c) => $this->q($c).'::text', explode(',', $cols)));
                $notNull = implode(' AND ', array_map(fn ($c) => $this->q($c).' IS NOT NULL', explode(',', $cols)));
                DB::statement(sprintf(
                    'INSERT INTO partition_unique_keys (scope, key_value) SELECT %s, %s FROM %s WHERE %s',
                    DB::getPdo()->quote($scope),
                    $expr,
                    $this->q($table),
                    $notNull
                ));
                $args[] = DB::getPdo()->quote($scope);
                $args[] = DB::getPdo()->quote($cols);
            }
            DB::statement(sprintf(
                'CREATE TRIGGER %s BEFORE INSERT OR UPDATE OR DELETE ON %s FOR EACH ROW EXECUTE FUNCTION partition_unique_guard(%s)',
                $this->q('trg_'.$table.'_unique_guard'),
                $this->q($table),
                implode(', ', $args)
            ));
        }

        DB::statement("ANALYZE {$this->q($table)}");

        return [
            'rows' => $rowCount,
            'uniques' => $uniques,
            'index_renames' => $renames,
            'sequences' => $sequences,
        ];
    }

    private function createSupportObjects(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS partition_unique_keys (
            scope varchar(150) NOT NULL,
            key_value text NOT NULL,
            PRIMARY KEY (scope, key_value)
        )');
        DB::statement('CREATE TABLE IF NOT EXISTS partition_conversion_log (
            id bigserial PRIMARY KEY,
            state jsonb NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        // Args: pairs of (scope, comma-separated columns).
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION partition_unique_guard() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE
    i int := 0;
    v_scope text;
    v_cols text[];
    v_new text;
    v_old text;
    c text;
    part text;
BEGIN
    WHILE i < TG_NARGS LOOP
        v_scope := TG_ARGV[i];
        v_cols := string_to_array(TG_ARGV[i + 1], ',');
        v_new := NULL; v_old := NULL;
        IF TG_OP <> 'DELETE' THEN
            v_new := '';
            FOREACH c IN ARRAY v_cols LOOP
                part := to_jsonb(NEW) ->> c;
                IF part IS NULL THEN v_new := NULL; EXIT; END IF;
                v_new := CASE WHEN v_new = '' THEN part ELSE v_new || '|' || part END;
            END LOOP;
        END IF;
        IF TG_OP <> 'INSERT' THEN
            v_old := '';
            FOREACH c IN ARRAY v_cols LOOP
                part := to_jsonb(OLD) ->> c;
                IF part IS NULL THEN v_old := NULL; EXIT; END IF;
                v_old := CASE WHEN v_old = '' THEN part ELSE v_old || '|' || part END;
            END LOOP;
        END IF;

        IF NOT (TG_OP = 'UPDATE' AND v_new IS NOT DISTINCT FROM v_old) THEN
            IF v_old IS NOT NULL THEN
                DELETE FROM partition_unique_keys WHERE scope = v_scope AND key_value = v_old;
            END IF;
            IF v_new IS NOT NULL THEN
                BEGIN
                    INSERT INTO partition_unique_keys (scope, key_value) VALUES (v_scope, v_new);
                EXCEPTION WHEN unique_violation THEN
                    RAISE EXCEPTION 'duplicate key value violates unique constraint "%"', v_scope
                        USING ERRCODE = 'unique_violation',
                              DETAIL = format('Key (%s)=(%s) already exists.', array_to_string(v_cols, ', '), v_new);
                END;
            END IF;
        END IF;
        i := i + 2;
    END LOOP;
    IF TG_OP = 'DELETE' THEN RETURN OLD; END IF;
    RETURN NEW;
END $$;
SQL);

        // Child side of a trigger FK: args (parent_table, parent_col, child_col).
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION partition_fk_child_guard() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE ok boolean;
BEGIN
    IF (to_jsonb(NEW) ->> TG_ARGV[2]) IS NULL THEN RETURN NEW; END IF;
    IF TG_OP = 'UPDATE' AND (to_jsonb(NEW) ->> TG_ARGV[2]) IS NOT DISTINCT FROM (to_jsonb(OLD) ->> TG_ARGV[2]) THEN
        RETURN NEW;
    END IF;
    EXECUTE format('SELECT EXISTS (SELECT 1 FROM %I p WHERE p.%I = $1.%I)', TG_ARGV[0], TG_ARGV[1], TG_ARGV[2])
        INTO ok USING NEW;
    IF NOT ok THEN
        RAISE EXCEPTION 'insert or update on table "%" violates foreign key to "%"', TG_TABLE_NAME, TG_ARGV[0]
            USING ERRCODE = 'foreign_key_violation',
                  DETAIL = format('Key (%s)=(%s) is not present in table "%s".', TG_ARGV[2], to_jsonb(NEW) ->> TG_ARGV[2], TG_ARGV[0]);
    END IF;
    RETURN NEW;
END $$;
SQL);

        // Parent side: args (child_table, child_col, parent_col, action RESTRICT|CASCADE|SET NULL).
        // AFTER trigger + existence re-check so a row merely moving partitions is not treated as deleted.
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION partition_fk_parent_guard() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE still_there boolean; has_children boolean;
BEGIN
    IF (to_jsonb(OLD) ->> TG_ARGV[2]) IS NULL THEN RETURN NULL; END IF;
    EXECUTE format('SELECT EXISTS (SELECT 1 FROM %I WHERE %I = $1.%I)', TG_TABLE_NAME, TG_ARGV[2], TG_ARGV[2])
        INTO still_there USING OLD;
    IF still_there THEN RETURN NULL; END IF;

    IF TG_ARGV[3] = 'CASCADE' THEN
        EXECUTE format('DELETE FROM %I WHERE %I = $1.%I', TG_ARGV[0], TG_ARGV[1], TG_ARGV[2]) USING OLD;
    ELSIF TG_ARGV[3] = 'SET NULL' THEN
        EXECUTE format('UPDATE %I SET %I = NULL WHERE %I = $1.%I', TG_ARGV[0], TG_ARGV[1], TG_ARGV[1], TG_ARGV[2]) USING OLD;
    ELSE
        EXECUTE format('SELECT EXISTS (SELECT 1 FROM %I WHERE %I = $1.%I)', TG_ARGV[0], TG_ARGV[1], TG_ARGV[2])
            INTO has_children USING OLD;
        IF has_children THEN
            RAISE EXCEPTION 'update or delete on table "%" violates foreign key from "%"', TG_TABLE_NAME, TG_ARGV[0]
                USING ERRCODE = 'foreign_key_violation';
        END IF;
    END IF;
    RETURN NULL;
END $$;
SQL);
    }

    /**
     * @param  array<string, string>  $fk
     */
    private function createTriggerForeignKey(array $fk): void
    {
        if (! preg_match('/FOREIGN KEY \((\w+)\) REFERENCES (?:public\.)?(\w+)\((\w+)\)/', $fk['def'], $m)) {
            throw new RuntimeException('Unsupported FK definition: '.$fk['def']);
        }
        [, $childCol, $parent, $parentCol] = $m;
        $action = str_contains($fk['def'], 'ON DELETE CASCADE') ? 'CASCADE'
            : (str_contains($fk['def'], 'ON DELETE SET NULL') ? 'SET NULL' : 'RESTRICT');
        $base = 'pfk_'.substr(md5($fk['name']), 0, 10);

        DB::statement(sprintf(
            'CREATE TRIGGER %s BEFORE INSERT OR UPDATE ON %s FOR EACH ROW EXECUTE FUNCTION partition_fk_child_guard(%s, %s, %s)',
            $this->q($base.'_c'), $this->q($fk['child']),
            DB::getPdo()->quote($parent), DB::getPdo()->quote($parentCol), DB::getPdo()->quote($childCol)
        ));
        DB::statement(sprintf(
            'CREATE TRIGGER %s AFTER DELETE ON %s FOR EACH ROW EXECUTE FUNCTION partition_fk_parent_guard(%s, %s, %s, %s)',
            $this->q($base.'_p'), $this->q($parent),
            DB::getPdo()->quote($fk['child']), DB::getPdo()->quote($childCol), DB::getPdo()->quote($parentCol), DB::getPdo()->quote($action)
        ));
    }

    /**
     * @param  array<int, string>  $targets
     * @return array<int, array{name: string, child: string, parent: string, def: string}>
     */
    private function foreignKeysTouching(array $targets): array
    {
        $rows = DB::select(
            "SELECT con.conname AS name, ch.relname AS child, pa.relname AS parent, pg_get_constraintdef(con.oid) AS def
             FROM pg_constraint con
             JOIN pg_class ch ON ch.oid = con.conrelid
             JOIN pg_class pa ON pa.oid = con.confrelid
             JOIN pg_namespace n ON n.oid = ch.relnamespace
             WHERE con.contype = 'f' AND n.nspname = current_schema()
               AND (ch.relname = ANY (?) OR pa.relname = ANY (?))",
            ['{'.implode(',', $targets).'}', '{'.implode(',', $targets).'}']
        );

        return array_map(fn ($r) => (array) $r, $rows);
    }

    private function isPlainTable(string $table): bool
    {
        $row = DB::selectOne(
            "SELECT c.relkind FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE c.relname = ? AND n.nspname = current_schema()",
            [$table]
        );

        return $row && $row->relkind === 'r';
    }

    private function relationExists(string $name): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 AS ok FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE c.relname = ? AND n.nspname = current_schema()',
            [$name]
        );
    }

    private function oid(string $table): int
    {
        return (int) DB::selectOne('SELECT ?::regclass::oid AS oid', [$table])->oid;
    }

    private function commonColumns(string $a, string $b): string
    {
        $cols = DB::select(
            "SELECT column_name FROM information_schema.columns WHERE table_name = ? AND table_schema = current_schema()
             AND column_name IN (SELECT column_name FROM information_schema.columns WHERE table_name = ? AND table_schema = current_schema())
             ORDER BY ordinal_position",
            [$a, $b]
        );

        return implode(', ', array_map(fn ($c) => $this->q($c->column_name), $cols));
    }

    private function q(string $ident): string
    {
        return '"'.str_replace('"', '""', $ident).'"';
    }
};

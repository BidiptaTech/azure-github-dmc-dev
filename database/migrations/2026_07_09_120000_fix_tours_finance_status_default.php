
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the column only if it does not exist.
        if (!Schema::hasColumn('tours', 'finance_status')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->string('finance_status')
                    ->default('pending');
            });
        }

        // Correct legacy values.
        DB::statement("
            UPDATE tours
            SET finance_status = 'pending'
            WHERE finance_status = 'draft'
               OR finance_status IS NULL
        ");

        // Set the required default.
        DB::statement("
            ALTER TABLE tours
            ALTER COLUMN finance_status SET DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Restore the previous default.
        // Do not drop the column because it may contain application data.
        if (Schema::hasColumn('tours', 'finance_status')) {
            DB::statement("
                ALTER TABLE tours
                ALTER COLUMN finance_status SET DEFAULT 'draft'
            ");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tours')) {
            Schema::table('tours', function (Blueprint $table) {
                if (! Schema::hasColumn('tours', 'refund_transaction_id')) {
                    $table->string('refund_transaction_id', 191)->nullable()->after('refund_amount');
                }
                if (! Schema::hasColumn('tours', 'refund_remarks')) {
                    $table->text('refund_remarks')->nullable()->after('refund_transaction_id');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'refund_transaction_id')) {
                    $after = Schema::hasColumn('orders', 'refund_amount') ? 'refund_amount' : null;
                    if ($after) {
                        $table->string('refund_transaction_id', 191)->nullable()->after($after);
                    } else {
                        $table->string('refund_transaction_id', 191)->nullable();
                    }
                }
                if (! Schema::hasColumn('orders', 'refund_remarks')) {
                    $after = Schema::hasColumn('orders', 'refund_transaction_id') ? 'refund_transaction_id' : null;
                    if ($after) {
                        $table->text('refund_remarks')->nullable()->after($after);
                    } else {
                        $table->text('refund_remarks')->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tours')) {
            Schema::table('tours', function (Blueprint $table) {
                if (Schema::hasColumn('tours', 'refund_remarks')) {
                    $table->dropColumn('refund_remarks');
                }
                if (Schema::hasColumn('tours', 'refund_transaction_id')) {
                    $table->dropColumn('refund_transaction_id');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'refund_remarks')) {
                    $table->dropColumn('refund_remarks');
                }
                if (Schema::hasColumn('orders', 'refund_transaction_id')) {
                    $table->dropColumn('refund_transaction_id');
                }
            });
        }
    }
};

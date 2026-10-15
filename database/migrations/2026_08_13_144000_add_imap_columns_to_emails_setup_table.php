<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('emails_setup')) {
            return;
        }

        Schema::table('emails_setup', function (Blueprint $table) {
            if (!Schema::hasColumn('emails_setup', 'IMAP_Host')) {
                $table->string('IMAP_Host')->nullable()->after('SMTP_Pass');
            }
            if (!Schema::hasColumn('emails_setup', 'IMAP_Port')) {
                $table->unsignedInteger('IMAP_Port')->nullable()->after('IMAP_Host');
            }
            if (!Schema::hasColumn('emails_setup', 'IMAP_Encrypt')) {
                $table->string('IMAP_Encrypt', 20)->nullable()->after('IMAP_Port');
            }
            if (!Schema::hasColumn('emails_setup', 'IMAP_User')) {
                $table->string('IMAP_User')->nullable()->after('IMAP_Encrypt');
            }
            if (!Schema::hasColumn('emails_setup', 'IMAP_Pass')) {
                $table->text('IMAP_Pass')->nullable()->after('IMAP_User');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('emails_setup')) {
            return;
        }

        Schema::table('emails_setup', function (Blueprint $table) {
            $columns = ['IMAP_Host', 'IMAP_Port', 'IMAP_Encrypt', 'IMAP_User', 'IMAP_Pass'];
            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('emails_setup', $column)));

            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};

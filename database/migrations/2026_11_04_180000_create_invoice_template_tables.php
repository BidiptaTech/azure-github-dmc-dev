<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DMC-owned invoice templates and immutable version history.
 *
 * Mirrors quotation_templates: ownership is users.userId (DMC role_id=11).
 * System-default designs stay in Blade under resources/views/invoices/pdf/.
 *
 * invoice_type values match PDF layout keys used by InvoiceController:
 *   proforma | proforma_price_only | final | final_price_only | alternate
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice_templates')) {
            Schema::create('invoice_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dmc_id');
                $table->enum('invoice_type', [
                    'proforma',
                    'proforma_price_only',
                    'final',
                    'final_price_only',
                    'alternate',
                ]);
                $table->unsignedBigInteger('active_version_id')->nullable();
                $table->timestamps();

                $table->unique(['dmc_id', 'invoice_type'], 'invoice_templates_dmc_type_unique');
                $table->foreign('dmc_id')
                    ->references('userId')
                    ->on('users')
                    ->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('invoice_template_versions')) {
            Schema::create('invoice_template_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_template_id');
                $table->unsignedInteger('version');
                $table->longText('html_content');
                $table->longText('css_content');
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->text('change_notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['invoice_template_id', 'version'],
                    'invoice_template_versions_template_version_unique'
                );

                $table->foreign('invoice_template_id')
                    ->references('id')
                    ->on('invoice_templates')
                    ->restrictOnDelete();

                $table->foreign('created_by')
                    ->references('userId')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('invoice_templates', function (Blueprint $table) {
                $table->foreign('active_version_id')
                    ->references('id')
                    ->on('invoice_template_versions')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_templates') && Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('invoice_templates', function (Blueprint $table) {
                $table->dropForeign(['active_version_id']);
            });
        }

        Schema::dropIfExists('invoice_template_versions');
        Schema::dropIfExists('invoice_templates');
    }
};

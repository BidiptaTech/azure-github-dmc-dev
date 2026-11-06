<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DMC-owned quotation templates and immutable version history.
 *
 * DMC ownership uses users.userId (there is no separate DMC table).
 * System-default designs stay in application code; this schema stores
 * custom DMC templates only.
 *
 * active_version_id is a nullable FK to quotation_template_versions.id.
 * It cannot also be a composite FK that forces the version to belong to
 * the same template: that composite would include quotation_templates.id,
 * which cannot be nulled on delete. The publishing service in a later
 * phase must set active_version_id transactionally to a published version
 * of the same template.
 *
 * SQLite cannot add this circular foreign key with ALTER TABLE, so the
 * active_version_id FK is created only on engines that support it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotation_templates')) {
            Schema::create('quotation_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dmc_id');
                $table->enum('quotation_type', ['itinerary', 'detailed']);
                $table->unsignedBigInteger('active_version_id')->nullable();
                $table->timestamps();

                $table->unique(['dmc_id', 'quotation_type'], 'quotation_templates_dmc_type_unique');
                $table->foreign('dmc_id')
                    ->references('userId')
                    ->on('users')
                    ->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('quotation_template_versions')) {
            Schema::create('quotation_template_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quotation_template_id');
                $table->unsignedInteger('version');
                $table->longText('html_content');
                $table->longText('css_content');
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->text('change_notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['quotation_template_id', 'version'],
                    'quotation_template_versions_template_version_unique'
                );

                // Restrict so published history is not removed if a template row is deleted.
                $table->foreign('quotation_template_id')
                    ->references('id')
                    ->on('quotation_templates')
                    ->restrictOnDelete();

                $table->foreign('created_by')
                    ->references('userId')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('quotation_templates', function (Blueprint $table) {
                $table->foreign('active_version_id')
                    ->references('id')
                    ->on('quotation_template_versions')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quotation_templates') && Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('quotation_templates', function (Blueprint $table) {
                $table->dropForeign(['active_version_id']);
            });
        }

        Schema::dropIfExists('quotation_template_versions');
        Schema::dropIfExists('quotation_templates');
    }
};

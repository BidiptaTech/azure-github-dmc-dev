<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create lost_found for fresh installs; add images column on existing tables.
     */
    public function up(): void
    {
        if (!Schema::hasTable('lost_found')) {
            Schema::create('lost_found', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dmc_id')->nullable()->index();
                $table->unsignedBigInteger('tour_id')->nullable()->index();
                $table->string('subject');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('description')->nullable();
                $table->boolean('resolved')->default(false);
                $table->json('comments')->nullable();
                $table->json('images')->nullable();
                $table->json('guest_images')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (!Schema::hasColumn('lost_found', 'images')) {
            Schema::table('lost_found', function (Blueprint $table) {
                $table->json('images')->nullable()->after('comments');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lost_found') && Schema::hasColumn('lost_found', 'images')) {
            Schema::table('lost_found', function (Blueprint $table) {
                $table->dropColumn('images');
            });
        }
    }
};

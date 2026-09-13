<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dmc_func_apps')) {
            return;
        }

        Schema::create('dmc_func_apps', function (Blueprint $table) {
            $table->id();
            $table->string('function_name')->unique();
            $table->unsignedInteger('maximum_limit');
            $table->json('dmc_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dmc_func_apps');
    }
};

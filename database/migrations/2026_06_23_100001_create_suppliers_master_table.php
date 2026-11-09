<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('countries', 'supplier_id')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('supplier_id');
            });
        }

        Schema::dropIfExists('suppliers_master');

        Schema::create('suppliers_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->unique()->constrained('countries')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers_master');
    }
};

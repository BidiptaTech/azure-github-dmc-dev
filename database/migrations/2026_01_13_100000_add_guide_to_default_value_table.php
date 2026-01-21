<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL compatible: Drop and recreate the constraint with the new values
        DB::statement("
            ALTER TABLE default_value 
            DROP CONSTRAINT IF EXISTS default_value_name_check;
        ");
        
        DB::statement("
            ALTER TABLE default_value 
            ADD CONSTRAINT default_value_name_check 
            CHECK (name IN ('hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port', 'guide'));
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'guide' from the check constraint
        DB::statement("
            ALTER TABLE default_value 
            DROP CONSTRAINT IF EXISTS default_value_name_check;
        ");
        
        DB::statement("
            ALTER TABLE default_value 
            ADD CONSTRAINT default_value_name_check 
            CHECK (name IN ('hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port'));
        ");
    }
};


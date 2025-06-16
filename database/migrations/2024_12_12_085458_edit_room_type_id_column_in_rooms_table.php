<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // DB::statement('ALTER TABLE rooms DROP CONSTRAINT IF EXISTS rooms_room_type_id_foreign;');
            // DB::statement('ALTER TABLE rooms RENAME COLUMN room_type_id TO room_type;');
            // DB::statement('ALTER TABLE rooms ALTER COLUMN room_type TYPE varchar(255) USING room_type::varchar(255);');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            //
             // Reverse the changes if rolling back
             $table->renameColumn('room_type', 'room_type_id');
             $table->integer('room_type_id')->change(); // original type
             // Assuming there was a foreign key constraint to drop during rollback
             $table->foreign('room_type_id')->references('id')->on('some_other_table');
        });
    }
};

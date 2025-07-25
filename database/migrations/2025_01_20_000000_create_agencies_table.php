 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('agency_id')->unique(); // Big Integer, Unique, Not Auto-Increment (handled in controller)
            $table->string('agency_name'); // Agency Name
            $table->string('email')->unique(); // Unique Email
            $table->string('phone'); // Contact Number
            $table->string('country'); // Country
            $table->string('city'); // City
            $table->text('address'); // Address
            $table->string('postal_code')->nullable(); // Postal Code
            $table->json('branches')->nullable(); // JSON array for branch data
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Inactive'); // Status
            $table->unsignedBigInteger('created_by')->nullable(); // Who created this agency
            $table->unsignedBigInteger('updated_by')->nullable(); // Who updated this agency
            $table->softDeletes(); // Soft Delete
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('created_by')->references('userId')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('userId')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
}; 
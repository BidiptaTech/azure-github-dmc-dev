<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails_setup', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dmcId')->unique();
            $table->string('From_Email')->nullable();
            $table->string('From_Name')->nullable();
            $table->string('SMTP_Host')->nullable();
            $table->unsignedInteger('SMTP_Port')->nullable();
            $table->string('SMTP_Encrypt', 20)->nullable();
            $table->string('SMTP_User')->nullable();
            $table->text('SMTP_Pass')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->text('email_footer')->nullable();
            $table->unsignedBigInteger('created_By')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails_setup');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the extended guest-profile fields. Field names that already exist
     * on the guests table get a "2" suffix (e.g. email -> email2, guest_id -> guest_id2).
     */
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // additionalInfo
            $table->string('guest_id2')->nullable();
            $table->string('languages')->nullable();
            $table->string('occupation')->nullable();

            // addressDetails
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state_region')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            // contactInfo
            $table->string('phone')->nullable();
            $table->string('email2')->nullable();
            $table->string('country_of_residence')->nullable();

            // emergencyContact
            $table->string('name')->nullable();
            $table->string('relationship')->nullable();
            $table->string('phone_number')->nullable();

            // fullNameAndGender
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();

            // healthInfo
            $table->string('allergies')->nullable();
            $table->string('disabilities')->nullable();
            $table->string('blood_group')->nullable();

            // passportDetails
            $table->string('passport_number')->nullable();
            $table->string('passport_nationality')->nullable();
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_expiry_date')->nullable();

            // paymentAndDocuments (nested objects stored as JSON)
            $table->json('payment_passport_details')->nullable();
            $table->json('government_approved_id')->nullable();

            // preferences
            $table->string('seat_preference')->nullable();
            $table->json('room_preference')->nullable();
            $table->string('meal_preference')->nullable();
            $table->string('dietary_type')->nullable();
            $table->json('personalization')->nullable();

            // travelSlamBook
            $table->string('favorite_place')->nullable();
            $table->text('travel_bucket_list')->nullable();
            $table->string('favourite_cuisine')->nullable();
            $table->string('favourite_colour')->nullable();
            $table->string('dream_destination')->nullable();
            $table->string('travel_mood')->nullable();
            $table->string('favourite_travel_companion')->nullable();
            $table->text('best_travel_memory')->nullable();
            $table->json('uploaded_images')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn([
                'guest_id2',
                'languages',
                'occupation',
                'address_line1',
                'address_line2',
                'city',
                'state_region',
                'postal_code',
                'country',
                'phone',
                'email2',
                'country_of_residence',
                'name',
                'relationship',
                'phone_number',
                'title',
                'first_name',
                'middle_name',
                'last_name',
                'full_name',
                'date_of_birth',
                'gender',
                'allergies',
                'disabilities',
                'blood_group',
                'passport_number',
                'passport_nationality',
                'passport_issue_date',
                'passport_expiry_date',
                'payment_passport_details',
                'government_approved_id',
                'seat_preference',
                'room_preference',
                'meal_preference',
                'dietary_type',
                'personalization',
                'favorite_place',
                'travel_bucket_list',
                'favourite_cuisine',
                'favourite_colour',
                'dream_destination',
                'travel_mood',
                'favourite_travel_companion',
                'best_travel_memory',
                'uploaded_images',
            ]);
        });
    }
};

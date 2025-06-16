<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/v1/login', 'App\Http\Controllers\Api\LoginControllerApi@login');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/zone-lists', 'App\Http\Controllers\Api\ZoneController@zone_lists');
    Route::get('/enquiry_lists', 'App\Http\Controllers\Api\EnquiryController@enquiry_lists');
    Route::post('/create-enquiry', 'App\Http\Controllers\Api\EnquiryController@createEnquiry');
    Route::get('/listofenquiry', 'App\Http\Controllers\Api\EnquiryController@listofenquiry');
    Route::post('/update-enquiry-form', 'App\Http\Controllers\Api\EnquiryController@UpdateEnquiryForm');
    Route::get('/zone-price', 'App\Http\Controllers\Api\ZoneController@zonewisePrice');
    Route::post('/create-enquiry-tour', 'App\Http\Controllers\Api\EnquiryController@enquiryToTour');

    Route::get('/hotel-details', 'App\Http\Controllers\Api\HotelController@details');

    Route::get('/facilities', 'App\Http\Controllers\Api\HotelController@facilities');
    Route::get('/category', 'App\Http\Controllers\Api\HotelController@category');
    Route::get('/location', 'App\Http\Controllers\Api\HotelController@index');
    Route::get('/details', 'App\Http\Controllers\Api\HotelController@hotelDetails');
    Route::get('/roomlists', 'App\Http\Controllers\Api\HotelController@roomLists');
    Route::post('/create-tour', 'App\Http\Controllers\Api\TourController@createTour');
    Route::get('/edit-tour', 'App\Http\Controllers\Api\TourController@editTour');
    Route::post('/create-booking', 'App\Http\Controllers\Api\TourController@createBooking');
    Route::post('/cancel-booking', 'App\Http\Controllers\Api\TourController@CancelBooking');
    Route::post('/update-enquiry', 'App\Http\Controllers\Api\TourController@updateEnquiry');
    Route::get('/enquiry-status', 'App\Http\Controllers\Api\TourController@enquiryStatus');
    Route::get('/get-pdf', 'App\Http\Controllers\Api\CountryPdf@GetPdf');
    Route::get('/get-cities', 'App\Http\Controllers\Api\CountryController@getCity');
    Route::get('/get-ports', 'App\Http\Controllers\Api\PortController@port_list');
    Route::get('/zone-vehicles', 'App\Http\Controllers\Api\ZoneController@vehicleLists');

    Route::get('/hotel-policies', 'App\Http\Controllers\Api\HotelPolicyController@fetchHotelPolicies');
    Route::get('/cancel-policies', 'App\Http\Controllers\Api\HotelPolicyController@fetchHotelCancellationPolicies');
    Route::get('/refund-policies', 'App\Http\Controllers\Api\HotelPolicyController@fetchHotelRefundPolicies');

    Route::get('/agents-list', 'App\Http\Controllers\Api\EnquiryController@agentLists');

    Route::get('/tour-list', 'App\Http\Controllers\Api\TourController@tourlists');
    Route::post('/tour-status', 'App\Http\Controllers\Api\TourController@TourStatus');
    Route::get('/restaurant', 'App\Http\Controllers\Api\RestaurantController@index');
    Route::get('/restaurant-details', 'App\Http\Controllers\Api\RestaurantController@restaurantDetails');
    Route::get('/attraction', 'App\Http\Controllers\Api\HomeController@attractionListing');
    Route::get('/attraction-details', 'App\Http\Controllers\Api\HomeController@attractionDetails');
    Route::get('/guide', 'App\Http\Controllers\Api\GuideController@index');
    Route::get('/guide-details', 'App\Http\Controllers\Api\GuideController@guideDetails');
    Route::get('/tour-details', 'App\Http\Controllers\Api\TourController@tourDetails');
    Route::post('/tour-delete', 'App\Http\Controllers\Api\TourController@deleteTour');
    Route::get('/rate-exchange', 'App\Http\Controllers\Api\RateExchange@exchangeRate');

    Route::get('/vehicles-list', 'App\Http\Controllers\Api\DriverController@vehicleListing');
    Route::get('/vehicle-details', 'App\Http\Controllers\Api\DriverController@vehicleDetails');
    Route::get('/hotels/{hotelId}/facilities', [App\Http\Controllers\RoomtypeController::class, 'getHotelFacilities']);
    Route::post('/logout', 'App\Http\Controllers\Api\LoginControllerApi@logout');

    Route::get('/cities', function (Request $request) {
        $input = $request->query('input');
        $response = Http::get("https://maps.googleapis.com/maps/api/place/autocomplete/json", [
            'input' => $input,
            'key' => "AIzaSyCLzISM9kkNCKKmQs7BcpSll4emFw1yicw",
            'types' => '(cities)',
        ]); 

        return $response->json();
    });
});
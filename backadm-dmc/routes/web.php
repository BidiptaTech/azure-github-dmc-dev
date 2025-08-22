<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\BedsController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\RoomtypeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\MasterSettingController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\HotelRestaurantController;
use App\Http\Controllers\HotelBookingController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\CustomPackageController;
use App\Http\Controllers\AttractionController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SingleTourPackageController;
use App\Http\Controllers\HotelCategoryController;
use App\Http\Controllers\OperationalCountryController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BookingAttractionController;
use App\Http\Controllers\BookingListController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\EnquiryListController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Controllers\SpecialDiscountController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\JobSheetController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceReportController;
use Illuminate\Support\Facades\Artisan;
use App\Services\AzureKeyVaultService;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PackagedAttractionController;

// Removed conflicting mobileapp routes - these should be in routes/mobileapp.php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Auth::routes();
Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return redirect()->route('dashboard');
})->name('clear');

Route::middleware(['auth'])->group(function () {
    // Tour creation route
    Route::post('/create-single-tour', [App\Http\Controllers\TourController::class, 'createTour'])->name('create.tour');
    Route::get('/', function () {
        return redirect()->route('dashboard'); // Redirects root to /index
    });
    
    // Updated dashboard routes to use the controller
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Custom Package Routes
    Route::get('/custom-packages/create', [CustomPackageController::class, 'create'])->name('custom-packages.create');
    
    // AJAX endpoints for dynamic functionality
    Route::post('/custom-packages/hotel-pricing', [CustomPackageController::class, 'getHotelPricing'])->name('custom-packages.hotel-pricing');
    Route::post('/custom-packages/activity-availability', [CustomPackageController::class, 'getActivityAvailability'])->name('custom-packages.activity-availability');
    Route::post('/custom-packages/vehicle-pricing', [CustomPackageController::class, 'getVehiclePricing'])->name('custom-packages.vehicle-pricing');
    Route::post('/custom-packages/location-suggestions', [CustomPackageController::class, 'getLocationSuggestions'])->name('custom-packages.location-suggestions');
    Route::post('/custom-packages/calculate-totals', [CustomPackageController::class, 'calculateRealTimeTotals'])->name('custom-packages.calculate-totals');
    Route::post('/custom-packages/add-hotel-service', [CustomPackageController::class, 'addHotelService'])->name('custom-packages.add-hotel-service');
    Route::post('/custom-packages/markup-suggestions', [CustomPackageController::class, 'getMarkupSuggestions'])->name('custom-packages.markup-suggestions');
    Route::post('/custom-packages/check-availability', [CustomPackageController::class, 'checkServiceAvailability'])->name('custom-packages.check-availability');
    Route::post('/custom-packages/currency-rates', [CustomPackageController::class, 'getCurrencyRates'])->name('custom-packages.currency-rates');
    Route::post('/custom-packages/calculate-pricing', [CustomPackageController::class, 'calculatePricing'])->name('custom-packages.calculate-pricing');
    Route::post('/custom-packages/validate', [CustomPackageController::class, 'validateQuote'])->name('custom-packages.validate');
    Route::post('/custom-packages/save-draft', [CustomPackageController::class, 'saveDraft'])->name('custom-packages.save-draft');
    Route::post('/custom-packages/save', [CustomPackageController::class, 'saveQuote'])->name('custom-packages.save');
    Route::post('/custom-packages/export-pdf', [CustomPackageController::class, 'exportToPDF'])->name('custom-packages.export-pdf');
    Route::post('/custom-packages/send-email', [CustomPackageController::class, 'sendQuoteEmail'])->name('custom-packages.send-email');
    
    Route::get('/dashboard/counts', [DashboardController::class, 'getCounts'])->name('dashboard.counts');

    // Add admin middleware to the hotels endpoint
    
    // Services Management Routes for DMC - MUST come BEFORE resource routes
    Route::get('/services/hotels', [HotelController::class, 'dmcHotelsSelection'])->name('services.hotels');
    Route::post('/services/hotels/update', [HotelController::class, 'updateDmcHotels'])->name('services.hotels.update');
    Route::post('/services/hotels/select', [HotelController::class, 'selectHotel'])->name('services.hotels.select');
    Route::post('/services/hotels/remove', [HotelController::class, 'removeHotel'])->name('services.hotels.remove');
    
    Route::get('/services/attractions', [AttractionController::class, 'dmcAttractionsSelection'])->name('services.attractions');
Route::post('/services/attractions/update', [AttractionController::class, 'updateDmcAttractions'])->name('services.attractions.update');
Route::post('/services/attractions/select', [AttractionController::class, 'selectAttraction'])->name('services.attractions.select');
Route::post('/services/attractions/remove', [AttractionController::class, 'removeAttraction'])->name('services.attractions.remove');
    
    Route::get('/services/restaurants', [RestaurantController::class, 'dmcRestaurantsSelection'])->name('services.restaurants');
Route::post('/services/restaurants/update', [RestaurantController::class, 'updateDmcRestaurants'])->name('services.restaurants.update');
Route::post('/services/restaurants/select', [RestaurantController::class, 'selectRestaurant'])->name('services.restaurants.select');
Route::post('/services/restaurants/remove', [RestaurantController::class, 'removeRestaurant'])->name('services.restaurants.remove');
    
    Route::resource('hotels', HotelController::class);
    
    Route::post('/search-roles', [FeaturesController::class, 'searchRoles'])->name('search-roles');
    Route::post('/get-all-roles', [FeaturesController::class, 'getAllRoles'])->name('get-all-roles');
    Route::get('/admin/dashboard', [UserController::class, 'adminlogin'])->name('admin.dashboard');
    Route::get('transaction', [UserController::class, 'transaction'])->name('transaction');
    Route::get('/admin/login-as/{userId}', [UserController::class, 'loginAsUser'])->name('admin.loginAsUser');
    Route::post('/update-price-comment', [EnquiryController::class, 'update'])->name('update-price-comment');
    //currency exchange rate
    Route::get('/exchange-rate', [CurrencyController::class, 'showExchangeRate'])->name('exchange-rate');
    Route::get('/get-exchange-rate', [CurrencyController::class, 'getExchangeRate'])->name('get-exchange-rate');
    
    // Single Tour Package Routes
    Route::get('/single-tour-package', [SingleTourPackageController::class, 'index'])->name('single-tour-package.index');
    Route::get('/single-tour-package/create', [SingleTourPackageController::class, 'create'])->name('single-tour-package.create');
    Route::get('/single-tour-package/thank-you', [SingleTourPackageController::class, 'thankYou'])->name('single-tour-package.thank-you');
    Route::post('/single-tour-package/thank-you', [SingleTourPackageController::class, 'thankYou']);
    Route::post('/single-tour-package', [SingleTourPackageController::class, 'store'])->name('single-tour-package.store');
    Route::post('/package-store-orders', [SingleTourPackageController::class, 'storeServiceOrders'])->name('single-tour-package.store-orders');
    Route::get('/single-tour-package/{id}', [SingleTourPackageController::class, 'show'])->name('single-tour-package.show');
    Route::get('/single-tour-package/{id}/edit', [SingleTourPackageController::class, 'edit'])->name('single-tour-package.edit');
    Route::put('/single-tour-package/{id}', [SingleTourPackageController::class, 'update'])->name('single-tour-package.update');
    Route::delete('/single-tour-package/{id}', [SingleTourPackageController::class, 'destroy'])->name('single-tour-package.destroy');

    // API routes for single tour packages (follow agent controller pattern)
    Route::get('/fetch-cities-by-country-single-tour', [SingleTourPackageController::class, 'fetchCitiesByCountry'])->name('fetch-cities-by-country-single-tour');
    Route::get('/fetch-attractions-by-dmc', [SingleTourPackageController::class, 'fetchAttractions'])->name('fetch-attractions-by-dmc');
    Route::get('/fetch-tickets-by-attraction', [SingleTourPackageController::class, 'fetchTickets'])->name('fetch-tickets-by-attraction');
    Route::get('/fetch-hotels-by-dmc', [SingleTourPackageController::class, 'fetchHotels'])->name('fetch-hotels-by-dmc');
    Route::get('/fetch-rooms-by-hotel', [SingleTourPackageController::class, 'fetchRooms'])->name('fetch-rooms-by-hotel');
    Route::get('/fetch-beds-by-room', [SingleTourPackageController::class, 'fetchBeds'])->name('fetch-beds-by-room');
    Route::get('/fetch-guides-by-dmc', [SingleTourPackageController::class, 'fetchGuides'])->name('fetch-guides-by-dmc');
    Route::get('/fetch-restaurants-by-dmc', [SingleTourPackageController::class, 'fetchRestaurants'])->name('fetch-restaurants-by-dmc');
    Route::get('/fetch-meals-by-restaurant', [SingleTourPackageController::class, 'fetchMealsByRestaurant'])->name('fetch-meals-by-restaurant');
    Route::get('/fetch-zones-by-dmc', [SingleTourPackageController::class, 'fetchZones'])->name('fetch-zones-by-dmc');
    Route::get('/fetch-vehicles-by-zones', [SingleTourPackageController::class, 'fetchVehiclesByZones'])->name('fetch-vehicles-by-zones');
    Route::post('/save-service', 'App\Http\Controllers\OrderController@saveService')->name('save-service');
    // authentication check for admin
    Route::group(['middleware' => ['admin']], function () {
       
        // Predefined Packages Routes
        // Country → City
        Route::get('/hotel-city/{city}', [PackageController::class, 'getHotelsByCity'])->name('hotel-city');

        Route::get('reports/sales-revenue', [FinanceReportController::class, 'salesRevenue'])->name('reports.sales-revenue');
        Route::get('reports/ledger', [FinanceReportController::class, 'ledger'])->name('reports.ledger');
        Route::get('reports/balance-sheet', [FinanceReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        
        // AJAX routes for ledger functionality
        Route::get('reports/transaction-details/{id}', [FinanceReportController::class, 'getTransactionDetails'])->name('reports.transaction-details');
        Route::get('reports/balance-history/{agentId}', [FinanceReportController::class, 'getBalanceHistory'])->name('reports.balance-history');
        Route::get('reports/export-transaction/{id}', [FinanceReportController::class, 'exportTransaction'])->name('reports.export-transaction');
        Route::get('reports/export-balance-history/{agentId}', [FinanceReportController::class, 'exportBalanceHistory'])->name('reports.export-balance-history');
        
        Route::get('/cities/{country}', [PackageController::class, 'getCitiesByCountry'])->name('cities-by-country');
        // City → Hotel
        // City → Attraction
        Route::get('/attractions/{city}', [PackageController::class, 'getAttractionsByCity'])->name('attractions-by-city');
        // City → Guide
        Route::get('/guides/{city}', [PackageController::class, 'getGuidesByCity'])->name('guides-by-city');
        // City → Restaurant
        Route::get('/restaurants/{city}', [PackageController::class, 'getRestaurantsByCity'])->name('restaurants-by-city');
        // City → Transport
        Route::get('/get-transport/{city}', [PackageController::class, 'getTransportByCity'])->name('transport-by-city');
        Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
// Route::get('/packages/{package_id}/edit', [PackageController::class, 'edit'])->name('packages.edit');
Route::put('/packages/{package_id}', [PackageController::class, 'update'])->name('packages.update');
Route::delete('/packages/{package_id}', [PackageController::class, 'destroy'])->name('packages.destroy');
Route::get('/packages/{package_id}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/packages-filtered', [PackageController::class, 'getFilteredPackages'])->name('packages.filtered');


        // Legacy route for backward compatibility
        Route::get('/package', [PackageController::class, 'index'])->name('package');
        Route::get('/predefined-package-booking-list', [PackageController::class, 'predefinedPackageBookingList'])->name('predefined.package.booking.list');
        Route::post('/package-booking/{booking_id}/add-payment', [PackageController::class, 'addPayment'])->name('package.add-payment');
        Route::post('/package-booking/{booking_id}/confirm-payment', [PackageController::class, 'confirmPayment'])->name('package.confirm-payment');

        Route::resource('zones', ZoneController::class);
        Route::post('/tour/{tourId}/verify-payment', [TourController::class, 'verifyPayment'])->name('tour.verify-payment');
        Route::post('/tour/{tourId}/decline-payment', [TourController::class, 'declinePayment'])->name('tour.decline-payment');
        Route::get('/get-ports', [HotelController::class, 'getPorts'])->name('get.ports');
        Route::POST('/cancel-book', [BookingListController::class, 'cancelBooking'])->name('booking.cancel');
        Route::POST('/approve-book', [BookingListController::class, 'approveBooking'])->name('booking.approve');
        Route::POST('/update-booking-dates', [BookingListController::class, 'updateBookingDates'])->name('booking.update.dates');

        Route::get('/get-tours-by-country/{country}', [ReportController::class, 'getToursByCountry'])->name('get.tours.by.country');
        Route::get('/get-tours-by-status', [ReportController::class, 'getToursByStatus'])->name('get.tours.by.status');

        Route::get('/countries/get-active', [ReportController::class, 'getActiveCountries'])->name('countries.get-active');
        Route::get('/reports/get-filtered-data', [ReportController::class, 'getFilteredData'])->name('reports.get-filtered-data');
        Route::post('/countries/toggle-status', [CountryController::class, 'toggleStatus'])->name('countries.toggle-status');
        Route::get('get-dmc-countries/{id}', [ReportController::class, 'getDmcCountries'])->name('get.dmc.countries');
        Route::get('get-master-dmc-countries/{id}', [ReportController::class, 'getMasterDmcCountries'])->name('get.master.dmc.countries');
        Route::get('/get-master-dmc', [ReportController::class, 'getMasterDmc'])->name('get.master.dmc');
        Route::get('/get-dmc', [ReportController::class, 'getDmc'])->name('get.dmc');
        Route::get('/enquiry', [EnquiryController::class, 'index'])->name('enquiry');
        Route::post('/enquiry/assign-manager', [EnquiryController::class, 'assignManager'])->name('enquiry.assign-manager');
        Route::post('/enquiry/remove-manager', [EnquiryController::class, 'removeManager'])->name('enquiry.remove-manager');
        Route::get('/revert-previous-user', [UserController::class, 'revertToPreviousUser'])->name('admin.revertPreviousUser');

        Route::post('/logout', [UserController::class, 'logout'])->name('logout');
        Route::post('/get-hotel-rooms', [HotelController::class, 'getHotelRooms'])->name('getHotelRooms');
        Route::post('bed/update', [BedsController::class, 'update'])->name('beds.update');
        Route::get('/get-bed-type-data', [HotelController::class, 'getBedTypeData'])->name('bed.type.data');
        Route::get('/get-user-details/{id}', [DriverController::class, 'getUserDetails'])->name('get-user-details');

        Route::get('/tours', [TourController::class, 'index'])->name('tours');
        Route::post('/tour/add-payment/{tourId}', [TourController::class, 'addPayment'])->name('tour.add-payment');
        Route::post('/tour/approve-booking/{tourId}', [TourController::class, 'approveBooking'])->name('tour.approve-booking');
        Route::post('/tour/assign-guide', [TourController::class, 'assignGuide'])->name('tour.assign-guide');
        Route::post('/tour/remove-guide', [TourController::class, 'removeGuide'])->name('tour.remove-guide');
        Route::post('/tour/assign-driver', [TourController::class, 'assignDriver'])->name('tour.assign-driver');
        Route::post('/tour/remove-driver', [TourController::class, 'removeDriver'])->name('tour.remove-driver');
        Route::get('/guides/search', [GuideController::class, 'search'])->name('guides.search');
        Route::get('/drivers/search', [GuideController::class, 'search'])->name('drivers.search');
        
        Route::get('/get-cities', [OperationalCountryController::class, 'getCities'])->name('getCities');
        Route::post('users/update-travclicks', [UserController::class, 'updateTravclicks'])->name('users.update.travclicks');
        Route::post('users/update-price-hide', [UserController::class, 'updatePriceHide'])->name('users.update.price-hide');
        Route::post('users/update-zone-on', [UserController::class, 'updateZone'])->name('update.zoneon');
        Route::post('users/update-email', [UserController::class, 'updateEmail'])->name('users.update.email');
        
        // Country and City API routes
        Route::get('/get-cities-name-country', [UserController::class, 'getCitiesByCountry'])->name('get.cities.by.country');
        Route::get('/get-country-code', [UserController::class, 'getCountryCode'])->name('get.country.code');

        Route::get('/get-no-of-rooms', [HotelController::class, 'getNoOfRooms'])->name('get-no-of-rooms');
        Route::get('/get-rooms-by-dmc', [HotelController::class, 'getRoomsByDmc']);
        Route::get('/api/get-dmc-cities/{dmcId}', [DriverController::class, 'getDmcCities'])->name('get.dmc.cities');
        Route::get('/features', [FeaturesController::class, 'index'])->name('features'); 
        Route::post('/save-feature-roles/{id}', [FeaturesController::class, 'saveFeatureRoles'])->name('save-feature-roles');
        Route::post('/update-status', [FeaturesController::class, 'statusUpdate'])->name('update-status');
        Route::get('master-setting', action: [MasterSettingController::class, 'index'])->name('master-setting');
        Route::post('store-setting', [MasterSettingController::class, 'store'])->name('store-setting');
        Route::resource('category', CategoryController::class);
        Route::resource('hotel-category', HotelCategoryController::class);
        Route::resource('facility', FacilityController::class);
        Route::resource('roomType', RoomtypeController::class);

        Route::get('/get-room-categories/{hotel_id}', [BedsController::class, 'getRoomCategories'])->name('get-room-categories');
        Route::get('beds/index', [BedsController::class, 'index'])->name('beds.index');
        Route::get('beds/create/{id}', [BedsController::class, 'create'])->name('beds.create');
        Route::post('beds/store', [BedsController::class, 'store'])->name('beds.store');
        Route::get('beds/edit/{id}', [BedsController::class, 'edit'])->name('beds.edit');
        Route::delete('beds/delete/{id}', [BedsController::class, 'destroy'])->name('beds.destroy');
        Route::resource('meals', MealController::class);
        Route::get('restaurant-meals/{restaurant_id}', [RestaurantController::class, 'restaurant_create'])->name('meals.restaurant_create');
        Route::get('restaurant/calendar/{restaurant_id}', [RestaurantController::class, 'restaurantCalendar'])->name('restaurant.calendar');
        Route::resource('restaurant', RestaurantController::class);
        Route::get('restaurant-hotel/{id}', [HotelRestaurantController::class, 'create'])->name('hotel-restaurant-create');

        Route::get('hotel-restaurant-edit/{id}', [HotelRestaurantController::class, 'edit'])->name('hotel-restaurant-edit');
        Route::post('hotel-restaurant-update/{id}', [HotelRestaurantController::class, 'update'])->name('hotel-restaurant-update');

        Route::get('hotel-restaurant-destroy/{id}', [HotelRestaurantController::class, 'destroy'])->name('hotel-restaurant-destroy');
        Route::post('hotel-restaurant/store', [HotelRestaurantController::class, 'store'])->name('hotel-restaurant-store');
        

        Route::get('hotel-meals/{dmc_id}/{hotel_id}', [HotelRestaurantController::class, 'mealsCreate'])->name('hotel-meals-create');
        Route::get('hotel-meal-edit/{id}', [HotelRestaurantController::class, 'mealEdit'])->name('hotel-meal-edit');
        Route::post('hotel-meals/store', [HotelRestaurantController::class, 'mealStore'])->name('hotel-meals-store');
        Route::post('hotel-meal-update/{id}', [HotelRestaurantController::class, 'mealUpdate'])->name('hotel-meal-update');
        Route::get('fetch-dmc-meals/{hotel_id}', [HotelRestaurantController::class, 'fetchDmcMeals'])->name('fetch.dmc.meals');
        Route::get('hotel-meal-destroy/{id}', [HotelRestaurantController::class, 'mealDestroy'])->name('hotel-meal-destroy');

        
        Route::get('attraction/calendar/{attraction_id}', [AttractionController::class, 'attractionCalendar'])->name('attraction.calendar');
        Route::resource('attraction', AttractionController::class);
        Route::get('guide/calendar/{guide_id}', [GuideController::class, 'guideCalendar'])->name('guide.calendar');

        // Packaged Attractions Routes
        Route::resource('packaged-attractions', PackagedAttractionController::class);
        Route::get('get-attractions', [PackagedAttractionController::class, 'getAttractions'])->name('get.attractions');
        Route::post('packaged-attractions/upload-images', [PackagedAttractionController::class, 'uploadImages'])->name('packaged-attractions.upload-images');
        Route::delete('packaged-attractions/remove-image/{id}', [PackagedAttractionController::class, 'removeImage'])->name('packaged-attractions.remove-image');

        Route::get('guide/guide-approval', [GuideController::class, 'guideApproval'])->name('guide.approval');
        Route::get('/edit-guide-approval/{guide}', [GuideController::class, 'editGuideApproval'])->name('guide.edit.approval');
        Route::put('/update-guide-approval/{guide}', [GuideController::class, 'updateGuideApproval'])->name('guide.update.approval');

        //job sheet
        Route::get('jobsheet/drivers', [JobSheetController::class, 'index'])->name('jobsheet.drivers');
        Route::get('jobsheet/create-driver-jobsheet', [JobSheetController::class, 'createDriverJobsheet'])->name('jobsheet.create.driver');
        Route::get('jobsheet/create-guide-jobsheet', [JobSheetController::class, 'createGuideJobsheet'])->name('jobsheet.create.guide');
        Route::get('jobsheet/view', [JobSheetController::class, 'viewJobsheets'])->name('jobsheet.view');

        Route::get('get-dmcs/{masterDmcId}', [JobSheetController::class, 'getDmcsByMaster'])->name('get.dmcs');
        Route::get('get-drivers/{dmcId}', [JobSheetController::class, 'getDriversByDmc'])->name('get.drivers');
        Route::get('get-driver-schedule/{driverId}', [JobSheetController::class, 'getDriverSchedule'])->name('get.driver.schedule');
        Route::get('get-tour-details/{tourId}', [JobSheetController::class, 'getTourDetails'])->name('get.tour.details');
        Route::get('get-tour-orders/{tourId}/{date}', [JobSheetController::class, 'getTourOrders'])->name('get.tour.orders');
        Route::get('get-orders-by-date/{date}', [JobSheetController::class, 'getOrdersByDate'])->name('get.orders.by.date');
        Route::get('get-tour-guide-orders/{tourId}/{date}', [JobSheetController::class, 'getTourGuideOrders'])->name('get.tour.guide.orders');
        Route::post('jobsheet/store/driver', [JobSheetController::class, 'storeDriverJobsheet'])->name('jobsheet.store.driver');
        Route::post('jobsheet/store/driver/assignments', [JobSheetController::class, 'storeDriverAssignments'])->name('jobsheet.store.driver.assignments');
        Route::post('jobsheet/store/guide', [JobSheetController::class, 'storeGuideJobsheet'])->name('jobsheet.store.guide');
        Route::post('update-driver-vehicle-assignment', [JobSheetController::class, 'updateDriverVehicleAssignment'])->name('update.driver.vehicle.assignment');
        Route::post('update-guide-jobsheet', [JobSheetController::class, 'updateGuideJobsheet'])->name('update.guide.jobsheet');
        
        // View jobsheets page and related endpoints
        Route::get('jobsheets/data', [JobSheetController::class, 'getJobsheetData'])->name('jobsheets.data');
        Route::get('jobsheets/export', [JobSheetController::class, 'exportJobsheets'])->name('jobsheets.export');

        Route::get('jobsheets/{id}', [JobSheetController::class, 'getJobsheetDetails'])->name('jobsheets.details');
        Route::get('get-tours', [JobSheetController::class, 'getAllTours'])->name('get.all.tours');
        
        //job sheet - guides
        Route::get('jobsheet/guides', [JobSheetController::class, 'indexGuide'])->name('jobsheet.guides');
        Route::get('get-guides/{dmcId}', [JobSheetController::class, 'getGuidesByDmc'])->name('get.guides');
        Route::get('get-guide-schedule/{guideId}', [JobSheetController::class, 'getGuideSchedule'])->name('get.guide.schedule');

        // Restaurant Approval
        Route::get('restaurants/restaurant-approval', [RestaurantController::class, 'restaurantApproval'])->name('restaurants.approval');
        Route::get('/edit-restaurant-approval/{restaurant}', [RestaurantController::class, 'editRestaurantApproval'])->name('restaurants.edit.approval');
        Route::put('/update-restaurant-approval/{restaurant}', [RestaurantController::class, 'updateRestaurantApproval'])->name('restaurant.update.approval');

        Route::resource('guide', GuideController::class);
        Route::resource('transport', TransportController::class);

        // Special Discount
        Route::resource('discount', SpecialDiscountController::class);
        // Vehicles
        Route::resource('vehicle', VehicleController::class);

        // In web.php routes
        Route::post('/vehicle/map-zones', [VehicleController::class, 'mapZones'])->name('vehicle.map_zones');

        Route::post('/vehicle/check-mapping-exists', [VehicleController::class, 'checkMappingExists'])->name('vehicle.check_mapping_exists');
        Route::post('/vehicle/add-mapping', [VehicleController::class, 'addMappingAjax'])->name('vehicle.add_mapping');
        Route::post('/vehicle/delete-mapping', [VehicleController::class, 'deleteMappingAjax'])->name('vehicle.delete_mapping');
        Route::post('/vehicle/restore-mapping', [VehicleController::class, 'restoreMappingAjax'])->name('vehicle.restore_mapping');

        // tickets
        Route::resource('tickets', TicketController::class);
        Route::get('tickets/add_ticket/{attraction_id}', [TicketController::class, 'add_ticket'])->name('tickets.add_ticket');
        Route::get('tickets/bulk_upload/{attraction_id}', [App\Http\Controllers\BulkUploadController::class, 'attractionTickets'])->name('tickets.bulk_upload_for_attraction');
        Route::post('tickets/bulk_upload/{attraction_id}', [App\Http\Controllers\BulkUploadController::class, 'uploadAttractionTickets'])->name('tickets.upload_for_attraction');
        Route::get('tickets/template/{attraction_id}', [App\Http\Controllers\BulkUploadController::class, 'downloadAttractionTicketTemplate'])->name('tickets.template_for_attraction');

        // meals bulk upload routes (individual routes without bulk-upload prefix)
        Route::get('meals/bulk_upload/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'restaurantMeals'])->name('meals.bulk_upload_for_restaurant');
        Route::post('meals/bulk_upload/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'uploadRestaurantMeals'])->name('meals.upload_for_restaurant');
        Route::get('meals/template/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'downloadRestaurantMealTemplate'])->name('meals.download_template_for_restaurant');

        // reports
        Route::resource('report', ReportController::class);

        //Country
        Route::resource('countries', CountryController::class);
        Route::resource('cities', CityController::class);


        // Mail Routes
        Route::prefix('mail')->name('mail.')->group(function () {
            // Main mail index
            Route::get('/', [MailController::class, 'index'])->name('index');
            
            // Template management
            Route::get('sync', [MailController::class, 'syncTemplates'])->name('sync');
            Route::get('templates/{type}/preview', [MailController::class, 'previewTemplate'])->name('templates.preview');
            
            // Email template previews
            Route::get('booking-confirmation', [MailController::class, 'bookingConfirmation'])->name('booking-confirmation');
            Route::get('booking-reminder', [MailController::class, 'bookingReminder'])->name('booking-reminder');
            Route::get('booking-cancellation', [MailController::class, 'bookingCancellation'])->name('booking-cancellation');
            Route::get('payment-confirmation', [MailController::class, 'paymentConfirmation'])->name('payment-confirmation');
            Route::get('tour-itinerary', [MailController::class, 'tourItinerary'])->name('tour-itinerary');
            Route::get('welcome-email', [MailController::class, 'welcomeEmail'])->name('welcome-email');
            Route::get('job-assignment', [MailController::class, 'jobAssignment'])->name('job-assignment');
            Route::get('enquiry-response', [MailController::class, 'enquiryResponse'])->name('enquiry-response');
            Route::get('feedback-request', [MailController::class, 'feedbackRequest'])->name('feedback-request');
            Route::get('agent-creation', [MailController::class, 'agentCreation'])->name('agent-creation');
            Route::get('agent-update', [MailController::class, 'agentUpdate'])->name('agent-update');
            
            // Email sending
            Route::post('send-booking-confirmation', [MailController::class, 'sendBookingConfirmation'])->name('send-booking-confirmation');
            
            // Settings management
            Route::get('settings', [MailController::class, 'settings'])->name('settings');
            Route::post('settings', [MailController::class, 'saveSettings'])->name('settings.save');
            Route::post('store-settings', [MailController::class, 'storeSettings'])->name('store-settings');
            
            // Test email
            Route::post('test', [MailController::class, 'testEmail'])->name('test');
        });

        //operational country
        Route::resource('country', OperationalCountryController::class);

        //Port management
        Route::get('/ports', [PortController::class, 'index'])->name('ports.index');
        Route::get('/ports/create', [PortController::class, 'create'])->name('ports.create');
        Route::post('/ports', [PortController::class, 'store'])->name('ports.store');
        Route::get('/ports/{port_id}', [PortController::class, 'show'])->name('ports.show');
        Route::get('/ports/{port_id}/edit', [PortController::class, 'edit'])->name('ports.edit');
        Route::put('/ports/{port_id}', [PortController::class, 'update'])->name('ports.update');
        Route::delete('/ports/{port_id}', [PortController::class, 'destroy'])->name('ports.destroy');
        Route::get('/port/get-cities', [PortController::class, 'getCities'])->name('port.getCities');
        Route::post('/port/toggle-status/{port_id}', [PortController::class, 'toggleStatus'])->name('port.toggle-status');

        Route::get('/fetch-cities', [OperationalCountryController::class, 'fetchCities'])->name('fetch.cities');
        Route::get('/get-existing-cities', [OperationalCountryController::class, 'getExistingCities'])->name('get.existing.cities');
        Route::get('/fetch-cities-countries', [GuideController::class, 'fetchCitiesCountries'])->name('fetch.cities_countries');
        Route::get('/fetch-dmc-cities', [VehicleController::class, 'fetchCities'])->name('fetch.dmc_cities');
        Route::get('/fetch-dmc-drivers', [VehicleController::class, 'fetchDrivers'])->name('fetch.dmc_drivers');

        //Booking List
        Route::resource('bookinglist', BookingListController::class);
        Route::get('/enquiries', [BookingListController::class, 'enquiry'])->name('bookinglist.enquiry');
        Route::get('tour-itinerary/{tourId}', [BookingListController::class, 'showItinerary'])->name('tour.itinerary');
        Route::post('bookinglist/update-date', [BookingListController::class, 'updateDate'])->name('bookinglist.updateDate');

        Route::resource('enquirylist', EnquiryListController::class);

        //Drivers Approval
        Route::get('driver/driver-approval', [DriverController::class, 'driverApproval'])->name('driver.approval');
        Route::get('/edit-driver-approval/{driver}', [DriverController::class, 'editdriverApproval'])->name('driver.edit.approval');
        Route::put('/update-driver-approval/{driver}', [DriverController::class, 'updateDriverApproval'])->name('driver.update.approval');

        //Drivers
        Route::get('driver/calendar/{driver_id}', [DriverController::class, 'driverCalendar'])->name('driver.calendar');
        Route::resource('driver', DriverController::class);
        
        Route::get('/hotels/search', [RoomtypeController::class, 'search'])->name('hotels.search');
        Route::get('/hotels/{hotelId}/facilities', [RoomtypeController::class, 'getHotelFacilities'])->name('hotels.facilities');
        // Route::get('/booking', [BookingController::class, 'index'])->name('booking');
        // Route::post('/booking/decline', [BookingController::class, 'decline'])->name('booking.decline');

        Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
        Route::post('/approve-booking', [BookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/decline-booking', [BookingController::class, 'decline'])->name('bookings.decline');
        
        // Hotel Booking Management Routes
        Route::post('/booking/update-hotel-dates', [HotelBookingController::class, 'updateHotelDates'])->name('booking.update.hotel.dates');
        Route::post('/booking/get-hotel-data', [HotelBookingController::class, 'getHotelBookingData'])->name('booking.get.hotel.data');

        // Attraction Booking Management Routes
        Route::post('/booking/update-attraction-booking', [HotelBookingController::class, 'updateAttractionBooking'])->name('booking.update.attraction.booking');
        Route::post('/booking/get-attraction-data', [HotelBookingController::class, 'getAttractionBookingData'])->name('booking.get.attraction.data');

        // Restaurant Booking Management Routes
        Route::post('/booking/update-restaurant-booking', [HotelBookingController::class, 'updateRestaurantBooking'])->name('booking.update.restaurant.booking');
        Route::post('/booking/get-restaurant-data', [HotelBookingController::class, 'getRestaurantBookingData'])->name('booking.get.restaurant.data');

        // Guide Booking Management Routes
        Route::post('/booking/update-guide-booking', [HotelBookingController::class, 'updateGuideBooking'])->name('booking.update.guide.booking');
        Route::post('/booking/get-guide-data', [HotelBookingController::class, 'getGuideBookingData'])->name('booking.get.guide.data');

        Route::post('/booking/update-arrival-booking', [HotelBookingController::class, 'updateArrivalBooking'])->name('booking.update.arrival.booking');
        Route::post('/booking/get-arrival-data', [HotelBookingController::class, 'getArrivalBookingData'])->name('booking.get.arrival.data');

        // Departure Booking Management Routes
        Route::post('/booking/update-departure-booking', [HotelBookingController::class, 'updateDepartureBooking'])->name('booking.update.departure.booking');
        Route::post('/booking/get-departure-data', [HotelBookingController::class, 'getDepartureBookingData'])->name('booking.get.departure.data');

        // Travel Point Booking Management Routes
        Route::post('/booking/update-travel-point-booking', [HotelBookingController::class, 'updateTravelPointBooking'])->name('booking.update.travel.point.booking');
        Route::post('/booking/get-travel-point-data', [HotelBookingController::class, 'getTravelPointBookingData'])->name('booking.get.travel.point.data');

        // Travel Hourly Booking Management Routes
        Route::post('/booking/update-travel-hourly-booking', [HotelBookingController::class, 'updateTravelHourlyBooking'])->name('booking.update.travel.hourly.booking');
        Route::post('/booking/get-travel-hourly-data', [HotelBookingController::class, 'getTravelHourlyBookingData'])->name('booking.get.travel.hourly.data');

        // Local Transport Booking Management Routes
        Route::post('/booking/update-local-transport-booking', [HotelBookingController::class, 'updateLocalTransportBooking'])->name('booking.update.local.transport.booking');
        Route::post('/booking/get-local-transport-data', [HotelBookingController::class, 'getLocalTransportBookingData'])->name('booking.get.local.transport.data');

        // Bookings Management Routes
        Route::get('/bookings/new-enquiries', [BookingsController::class, 'newEnquiries'])->name('bookings.new-enquiries');
        Route::get('/bookings/follow-ups', [BookingsController::class, 'followUps'])->name('bookings.follow-ups');
        Route::get('/bookings/tentative', [BookingsController::class, 'tentative'])->name('bookings.tentative');
        Route::get('/bookings/confirmed', [BookingsController::class, 'confirmedBookings'])->name('bookings.confirmed');
        Route::get('/bookings/definite', [BookingsController::class, 'definiteBookings'])->name('bookings.definite');
        Route::get('/bookings/actual', [BookingsController::class, 'actualBookings'])->name('bookings.actual');
        Route::get('/bookings/cancelled', [BookingsController::class, 'cancelledBookings'])->name('bookings.cancelled');
        Route::get('/bookings/refunds', [BookingsController::class, 'refunds'])->name('bookings.refunds');
        Route::post('/bookings/process-refund', [BookingsController::class, 'processRefund'])->name('bookings.process-refund');
        Route::get('/bookings/cancellations-refunds', [BookingsController::class, 'cancellationsRefunds'])->name('bookings.cancellations-refunds');
        Route::get('/bookings/stats', [BookingsController::class, 'getBookingStats'])->name('bookings.stats');
        Route::get('/bookings/view-tour/{tourId}', [BookingsController::class, 'viewTour'])->name('bookings.view-tour');
        Route::get('/bookings/export-tour-pdf/{tourId}', [BookingsController::class, 'exportTourPDF'])->name('bookings.export-tour-pdf');
        Route::post('/booking/approve-hotel-booking', [HotelBookingController::class, 'approveHotelBooking'])->name('booking.approve.hotel.booking');
        Route::post('/booking/reject-hotel-booking', [HotelBookingController::class, 'rejectHotelBooking'])->name('booking.reject.hotel.booking');
        Route::post('/booking/approve-attraction-booking', [HotelBookingController::class, 'approveAttractionBooking'])->name('booking.approve.attraction.booking');
        Route::post('/booking/reject-attraction-booking', [HotelBookingController::class, 'rejectAttractionBooking'])->name('booking.reject.attraction.booking');

        // Route::get('/approve-attraction', [BookingAttractionController::class, 'index'])->name('booking.attraction');
        // Route::post('/booking-attraction/approve', [BookingAttractionController::class, 'approve'])->name('booking.attraction.approve');
        // Route::post('/booking-attraction/decline', [BookingAttractionController::class, 'decline'])->name('booking.attraction.decline');

        Route::get('/hotel/conference/{id}', [HotelController::class, 'conference'])->name('hotel.conference');
        Route::post('/update/conference', [HotelController::class, 'updateConference'])->name('update.conference');
        Route::get('hotel_details/{hotel}', [HotelController::class, 'hotelDetails'])->name('hotel_details');
        Route::get('hotel_brand_details/{brand}', [HotelController::class, 'hotelBrandDetails'])->name('hotel_brand_details');
        
        Route::post('/roomType/toggle', [RoomTypeController::class, 'toggle'])->name('roomType.toggle');
        Route::get('hotel/facility/{id}', [HotelController::class, 'hotelfacility'])->name('hotels.facility');
        Route::POST('facility/store', [HotelController::class, 'storeFacility'])->name('store.facility.image');
        Route::POST('facility/image', [HotelController::class, 'updateFacility'])->name('upload.facility.image');
        Route::get('/facility/{facilityId}/edit/{hotelId}', [HotelController::class, 'editfacility'])->name('edit.facility');
        Route::post('/hotel/{hotelId}/facility/{facilityId}', [HotelController::class, 'destroyfacility'])->name('facilities.destroy');
        Route::get('/hotelport/{id}', [HotelController::class, 'showPorts'])->name('hotelp');

        Route::post('updateports', [HotelController::class, 'updateports'])->name('updateports');
        Route::get('policy/{id}', [HotelController::class, 'policy'])->name('policy');
        // Route::get('cancellation/policy/{id}', [HotelController::class, 'cancellationPolicy'])->name('cancellation.policy');
        // Route::get('refund/policy/{id}', [HotelController::class, 'refundPolicy'])->name('refund.policy');

        Route::get('hotels/hotel-approval', [HotelController::class, 'hotelApproval'])->name('hotels.approval');
        Route::get('/edit-hotel-approval/{hotel}', [HotelController::class, 'editHotelApproval'])->name('hotels.edit.approval');
        Route::put('/update-hotel-approval/{hotel}', [HotelController::class, 'updateHotelApproval'])->name('hotels.update.approval');
        
        Route::post('/update/refundPolicy', [HotelController::class, 'updateRefundPolicy'])->name('updaterefund.policy');
        Route::post('/update/cancellationPolicy', [HotelController::class, 'updatecancellationPolicy'])->name('updatecancellation.policy');
        Route::post('/update/policy', [HotelController::class, 'updatepolicy'])->name('update.policy');
        Route::post('/update/child-policy', [HotelController::class, 'updateChildPolicy'])->name('updatechild.policy');
        Route::post('/update/pet-policy', [HotelController::class, 'updatePetPolicy'])->name('updatepet.policy');
        Route::post('/update/terms-policy', [HotelController::class, 'updateTermsPolicy'])->name('updateterms.policy');
        Route::get('hotel/calendar/{hotel_unique_id}', [HotelController::class, 'hotelCalendar'])->name('hotels.viewcalendar');
        
        Route::get('calender/{hotel}', [HotelController::class, 'calender'])->name('hotels.calender');
        Route::get('yearly/calender', [HotelController::class, 'yearlycalender'])->name('hotels.yearlycalender');
        Route::get('/hotel/rooms', [HotelController::class, 'hotelrooms'])->name('hotels.room');
        Route::get('/hotel/create/rooms/{id}', [HotelController::class, 'createHotelRooms'])->name('hotels.createroom');

        // Bulk Upload Routes
        Route::prefix('bulk-upload')->name('bulk-upload.')->group(function () {
            Route::get('/hotels', [App\Http\Controllers\BulkUploadController::class, 'hotels'])->name('hotels');
            Route::post('/hotels', [App\Http\Controllers\BulkUploadController::class, 'uploadHotels'])->name('hotels.upload');
            Route::get('/hotels/template', [App\Http\Controllers\BulkUploadController::class, 'downloadHotelTemplate'])->name('hotels.template');
            
            Route::get('/drivers', [App\Http\Controllers\BulkUploadController::class, 'drivers'])->name('drivers');
            Route::post('/drivers', [App\Http\Controllers\BulkUploadController::class, 'uploadDrivers'])->name('drivers.upload');
            Route::get('/drivers/template', [App\Http\Controllers\BulkUploadController::class, 'downloadDriverTemplate'])->name('drivers.template');
            
            Route::get('/guides', [App\Http\Controllers\BulkUploadController::class, 'guides'])->name('guides');
            Route::post('/guides', [App\Http\Controllers\BulkUploadController::class, 'uploadGuides'])->name('guides.upload');
            Route::get('/guides/template', [App\Http\Controllers\BulkUploadController::class, 'downloadGuideTemplate'])->name('guides.template');
            
            Route::get('/restaurants', [App\Http\Controllers\BulkUploadController::class, 'restaurants'])->name('restaurants');
            Route::post('/restaurants', [App\Http\Controllers\BulkUploadController::class, 'uploadRestaurants'])->name('restaurants.upload');
            Route::get('/restaurants/template', [App\Http\Controllers\BulkUploadController::class, 'downloadRestaurantTemplate'])->name('restaurants.template');
            
            Route::get('/vehicles', [App\Http\Controllers\BulkUploadController::class, 'vehicles'])->name('vehicles');
            Route::post('/vehicles', [App\Http\Controllers\BulkUploadController::class, 'uploadVehicles'])->name('vehicles.upload');
            Route::get('/vehicles/template', [App\Http\Controllers\BulkUploadController::class, 'downloadVehicleTemplate'])->name('vehicles.template');
            
            Route::get('/attractions', [App\Http\Controllers\BulkUploadController::class, 'attractions'])->name('attractions');
            Route::post('/attractions', [App\Http\Controllers\BulkUploadController::class, 'uploadAttractions'])->name('attractions.upload');
            Route::get('/attractions/template', [App\Http\Controllers\BulkUploadController::class, 'downloadAttractionTemplate'])->name('attractions.template');
            
            // Ticket Bulk Upload Routes - Only for DMC (role_id = 11)
            Route::get('/tickets', [App\Http\Controllers\BulkUploadController::class, 'tickets'])->name('tickets');
            Route::post('/tickets/{attraction_id}', [App\Http\Controllers\BulkUploadController::class, 'uploadAttractionTickets'])->name('tickets.upload');
            Route::get('/tickets/template/{attraction_id}', [App\Http\Controllers\BulkUploadController::class, 'downloadAttractionTicketTemplate'])->name('tickets.template');
            
            // Meal Bulk Upload Routes - Only for DMC (role_id = 11)
            Route::get('/meals', [App\Http\Controllers\BulkUploadController::class, 'meals'])->name('meals');
            Route::post('/meals/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'uploadMeals'])->name('meals.upload');
            Route::get('/meals/template/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'downloadMealTemplate'])->name('meals.template');
            
            // Restaurant-specific meal bulk upload routes
            Route::get('meals/bulk_upload/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'restaurantMeals'])->name('meals.bulk_upload_for_restaurant');
            Route::post('meals/bulk_upload/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'uploadRestaurantMeals'])->name('meals.upload_for_restaurant');
            Route::get('meals/template/{restaurant_id}', [App\Http\Controllers\BulkUploadController::class, 'downloadRestaurantMealTemplate'])->name('meals.download_template_for_restaurant');
        });


        Route::get('/hotels/{hotel}/contact', [HotelController::class, 'hotelcontacts'])->name('hotels.contact');
        Route::post('/updatecontacts', [HotelController::class, 'updatecontacts'])->name('hotels.createcontacts');

        Route::get('/hotels/{hotel}/events', [HotelController::class, 'hotelrates'])->name('hotels.rates');
        Route::post('storeEvents', [HotelController::class, 'storerates'])->name('storerates');
        Route::get('editevents/{id}/{hotel_id}', [HotelController::class, 'editrate'])->name('rates.edit');
        Route::post('updaterates', [HotelController::class, 'updaterates'])->name('rates.update');
        Route::delete('deleterates/{id}', [HotelController::class, 'deleterate'])->name('rates.destroy');

        Route::get('/hotels/{hotel}/season', [HotelController::class, 'hotelseason'])->name('hotels.season');
        Route::post('storeseason', [HotelController::class, 'storeseason'])->name('storeseason');
        Route::get('editseason/{id}/{hotel_id}', [HotelController::class, 'editseason'])->name('season.edit');
        Route::post('updateseason', [HotelController::class, 'updateseason'])->name('season.update');
        Route::delete('deleteseason/{hotel_id}/{id}', [HotelController::class, 'deleteSeason'])->name('season.destroy');
        
        // Season Bulk Upload Routes - Only for DMC (role_id = 11)
        Route::get('seasons/bulk_upload/{hotel_id}', [App\Http\Controllers\BulkUploadController::class, 'hotelSeasons'])->name('seasons.bulk_upload_for_hotel');
        Route::post('seasons/bulk_upload/{hotel_id}', [App\Http\Controllers\BulkUploadController::class, 'uploadHotelSeasons'])->name('seasons.upload_for_hotel');
        Route::get('seasons/template/{hotel_id}', [App\Http\Controllers\BulkUploadController::class, 'downloadHotelSeasonTemplate'])->name('seasons.template_for_hotel');

        Route::get('/editcontacts/{hotel}', [HotelController::class, 'editcontacts'])->name('contactdetails.edit');
        
        Route::post('storeroom', [HotelController::class, 'storeroom'])->name('storeroom');
        Route::get('editroom/{id}', [HotelController::class, 'editroom'])->name('rooms.edit');
        Route::post('updateroom', [HotelController::class, 'updateroom'])->name('room.update');
        Route::delete('deleteroom/{id}', [HotelController::class, 'deleteroom'])->name('rooms.destroy');
        Route::post('update-base-room', [RoomtypeController::class, 'updateBaseRoom'])->name('rooms.update-base-room');
        Route::post('update-rooms-only', [RoomtypeController::class, 'updateRoomsOnly'])->name('rooms.update-rooms-only');

        Route::get('/hotels/{hotel}/beds', [HotelController::class, 'hotelbeds'])->name('hotels.beds');
        Route::post('storebeds', [HotelController::class, 'storebeds'])->name('storebed');
        Route::get('edit_bed/{id}/{hotel_id}', [HotelController::class, 'editbed'])->name('bed.edit');
        Route::post('updatebed', [HotelController::class, 'updatebed'])->name('bed.update');
        Route::delete('deletebed/{hotelId}/{bedId}', [HotelController::class, 'deletebed'])->name('bed.destroy');
        
        // Bed Bulk Upload Routes - Only for DMC (role_id = 11)
        Route::get('beds/bulk_upload/{hotel_id}', [App\Http\Controllers\BulkUploadController::class, 'hotelBeds'])->name('beds.bulk_upload_for_hotel');
        Route::post('beds/bulk_upload/{hotel_id}', [App\Http\Controllers\BulkUploadController::class, 'uploadHotelBeds'])->name('beds.upload_for_hotel');
        Route::get('beds/template/{hotel_id}', [App\Http\Controllers\BulkUploadController::class, 'downloadHotelBedTemplate'])->name('beds.template_for_hotel');
        
        // Route::get('/pending-attractions', [AttractionController::class, 'pendingAttraction'])->name('attraction.pending');
        Route::get('attractions/attraction-approval', [AttractionController::class, 'attractionApproval'])->name('attractions.approval');
        Route::get('/edit-attraction-approval/{attraction}', [AttractionController::class, 'editAttractionApproval'])->name('attractions.edit.approval');
        Route::put('/update-attraction-approval/{attraction}', [AttractionController::class, 'updateAttractionApproval'])->name('attraction.update.approval');

        Route::post('attractionCloseDate', [AttractionController::class, 'attractionCloseDate'])->name('attraction_close_dates');
        Route::post('driverCloseDate', [DriverController::class, 'driverCloseDate'])->name('driver_close_dates');
        Route::post('guideCloseDate', [GuideController::class, 'guideCloseDate'])->name('guide_close_dates');
        Route::post('restaurantCloseDate', [RestaurantController::class, 'restaurantCloseDate'])->name('restaurant_close_dates');
        Route::post('hotelCloseDate', [HotelController::class, 'hotelCloseDate'])->name('hotel_close_dates');

        Route::resource('agents', AgentController::class);
        Route::get('/get-sales-manager-details/{userId}', [AgentController::class, 'getSalesManagerDetails'])->name('get-sales-manager-details');
        Route::get('/get-cities-by-country', [AgentController::class, 'fetchCitiesByCountry'])->name('fetch-cities-by-country');
        Route::get('/fetch-country-code', [AgentController::class, 'fetchCountryCode'])->name('fetch-country-code');
        Route::post('/update-agent-dmc', [AgentController::class, 'updateDmcId'])->name('agents.update-dmc');

        // Agency routes
        Route::get('/agencies/get-cities-by-country', [AgencyController::class, 'getCitiesByCountry'])->name('agencies.getCitiesByCountry');
        Route::resource('agencies', AgencyController::class);
        Route::patch('/agencies/{id}/toggle-status', [AgencyController::class, 'toggleStatus'])->name('agencies.toggleStatus');

        Route::get('/search-agents', [App\Http\Controllers\AgentController::class, 'searchAgents'])->name('search-agents');
        Route::resource('users', UserController::class);
        Route::get('/get-countries/{masterDmcId}', [UserController::class, 'getCountries'])->name('get-countries');
        Route::get('/get-markup/{selectedCountry}', [UserController::class, 'selectedCountry'])->name('get-markup');
        Route::get('/get-assistant-manager/{country}', [UserController::class, 'getAssistantManagers'])->name('get-assistant-manager');
        Route::post('/admin/get-countries-by-master-dmc', [UserController::class, 'getCountriesByMasterDmc'])->name('admin.get-countries-by-master-dmc');
        Route::post('/admin/get-sales-managers-by-master-dmc', [UserController::class, 'getSalesManagersByMasterDmc'])->name('admin.get-sales-managers-by-master-dmc');

        Route::resource('roles', RoleController::class);  
        Route::get('/get-roles-by-user-type/{userType}', [UserController::class, 'getRolesByUserType'])->name('get-roles-by-user-type');
        
        Route::post('add-money/{id}', [UserController::class, 'add_money'])->name('add-money');
        // Route::post('/guide/approve-or-decline/{guideId}', [GuideController::class, 'approveOrDecline']);
        // Route::post('/driver/approve-or-decline/{driverId}', [DriverController::class, 'approveOrDecline']);

        //Enquiry
        
        Route::post('/zones/{zone}/settings', [ZoneController::class, 'saveSettings'])->name('zones.settings');
        // Route::post('/cities/store', [PortController::class, 'store'])->name('cities.store');

        // Restaurant Coupon
        Route::post('/generate-restaurant-coupon', [RestaurantController::class, 'generateCoupon'])->name('generate.restaurant.coupon');
        Route::get('/view-voucher-image/{booking_id}/{tour_id}', [RestaurantController::class, 'viewVoucherImage'])->name('view.voucher.image');
        Route::resource('restaurant', RestaurantController::class);

        // Agent View routes
        Route::get('/registered-agents', [App\Http\Controllers\AgentViewController::class, 'index'])->name('registered-agents.index');
        Route::get('/registered-agents/{agent_id}', [App\Http\Controllers\AgentViewController::class, 'show'])->name('registered-agents.show');
        Route::post('/registered-agents/verify', [App\Http\Controllers\AgentViewController::class, 'verifyAgent'])->name('registered-agents.verify');
    });

    //authentication check for manager (route can access admin & manager)
    Route::group(['middleware' => ['manager']], function () {
        Route::post('/tour/{tourId}/verify-payment', [TourController::class, 'verifyPayment'])->name('tour.verify-payment');
        Route::post('/tour/{tourId}/decline-payment', [TourController::class, 'declinePayment'])->name('tour.decline-payment');
    });

});    

// Job Sheet routes added to the admin middleware group above

// Package Routes

// Add this route for testing booking confirmation email
Route::get('/test-booking-email', function() {
    try {
        // Prepare dynamic data for the booking confirmation email
        $data = [
            "booking_id" => "BK-" . rand(10000, 99999),
            "customer_name" => "John Doe",
            "type" => "Hotel Booking",
            "booking_date" => date('Y-m-d'),
            "check_in_date" => date('Y-m-d', strtotime('+7 days')),
            "check_out_date" => date('Y-m-d', strtotime('+10 days')),
            "location" => "Paris, France",
            "guests" => "2 Adults, 1 Child",
            "reference_number" => "REF-" . rand(1000, 9999),
            "total_price" => 1250.00,
            "payment_status" => "Paid"
        ];
        
        // Send email using CommonHelper
        $email = "saurabh.coactive@gmail.com";
        $type = "confirmation";
        $subject = "Your Booking Confirmation #" . $data['booking_id'];
        $body = "Thank you for your booking with us!";
        
        \App\Helpers\CommonHelper::sendEmail($email, $type, $subject, $body, $data);
        
        return [
            'success' => true,
            'message' => 'Booking confirmation email sent successfully!',
            'booking_id' => $data['booking_id']
        ];
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Email error: ' . $e->getMessage());
        
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
});

Route::get('{routeName}/{name?}', [HomeController::class, 'pageView']); 










import { configureStore } from "@reduxjs/toolkit";
import findPlaceSlice from "../features/hero/findPlaceSlice";
import attractionsReducer from "../slice/attractions/attractionSlice";
import authReducer from "../slice/common/authSlices";
import listsReducer from "../slice/common/TourlistSlice";
import categoryReducer from "../slice/hotel/CategorySlice";
import hotelsReducer from "../slice/hotel/hotelSlice";
import hoteldetailsReducer from "../slice/hotel/HotelDetailsSlice";
import loginReducer from "@/pages/login/loginSlice";
import HotelAvailabilityReducer from "../slice/hotel/HotelAvailabilitySlice";
import bookingReducer from "@/slice/common/BookingSlice";
import enquiryReducer from "@/slice/common/EnquirySlice";
import cityReducer from "@/slice/common/citySlice";
import editingReducer from "@/slice/common/EditSlice";
import stepsReducer from "@/slice/common/stepsSlice";
import pickupDropReducer from "@/slice/port/pickupDropSlice";
import localtourReducer from "@/slice/localtour/Localslice";
import tourguideReducer from "@/slice/tourguide/guideslice";
import restaurantsReducer from "@/slice/restaurant/RestaurantsSlice";
import dateServiceReducer from "@/slice/common/dateServicesSlice";
import viewDetailsReducer from "../slice/common/ViewDetails";
import customerInfoReducer from "../slice/common/customerInfo";
import commonReducer from "../slice/common/commonSlice";
import enquiryListReducer from "../slice/common/enquiryListSlice";
import citiesReducer from "../slice/common/citiesSlice";
import bookingEnquiryReducer from "../slice/enquiries/enquiryListSlice";
import agentListReducer from "../slice/common/agentListSlice"
import convertToTourListReducer from "../slice/enquiries/enquiryToTourSlice"
import tourPackagesReducer from "../slice/tour-packages/tourPackageSlice"
import prePackagesReducer from "../slice/tour-packages/prePackagesSlice"

export const store = configureStore({
  reducer: {
    hero: findPlaceSlice,
    attractions: attractionsReducer,
    auth: authReducer,
    lists: listsReducer,
    hotels: hotelsReducer,
    category: categoryReducer,
    hoteldetails: hoteldetailsReducer,
    login: loginReducer,
    rooms: HotelAvailabilityReducer,
    bookings: bookingReducer,
    enquiry: enquiryReducer,
    city: cityReducer,
    editing: editingReducer,
    steps: stepsReducer,
    pickupDrop: pickupDropReducer,
    localtour: localtourReducer,
    tourguide: tourguideReducer,
    restaurants: restaurantsReducer,
    dateService: dateServiceReducer,
    viewDetails: viewDetailsReducer,
    customerInfo: customerInfoReducer,
    common: commonReducer,
    enquiryList: enquiryListReducer,
    cities: citiesReducer,
    bookingEnquiry: bookingEnquiryReducer,
    agentList:agentListReducer,
    convertToTourList:convertToTourListReducer,
    tourPackages: tourPackagesReducer,
    prePackages: prePackagesReducer
  },
});

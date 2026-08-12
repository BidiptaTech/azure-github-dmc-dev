import { configureStore } from "@reduxjs/toolkit";
import findPlaceSlice from "../features/hero/findPlaceSlice";
import attractionsReducer, {
  setAttractionService,
} from "../slice/attractions/attractionSlice";
import authReducer, { setTourIdd } from "../slice/common/authSlices";
import listsReducer from "../slice/common/TourlistSlice";
import categoryReducer from "../slice/hotel/CategorySlice";
import hotelsReducer, {
  setId,
  setHotelService,
  settourdetails,
  updateSearchState,
} from "../slice/hotel/hotelSlice";
import hoteldetailsReducer from "../slice/hotel/HotelDetailsSlice";
import loginReducer from "@/pages/login/loginSlice";
import HotelAvailabilityReducer from "../slice/hotel/HotelAvailabilitySlice";
import bookingReducer, {
  setCheckIn,
  setCheckOut,
  setSearchLocation,
  setGuest,
} from "@/slice/common/BookingSlice";
import enquiryReducer from "@/slice/common/EnquirySlice";
import cityReducer, { setCity } from "@/slice/common/citySlice";
import editingReducer, { setTourId1 } from "@/slice/common/EditSlice";
import stepsReducer, { setTourId } from "@/slice/common/stepsSlice";
import pickupDropReducer, {
  setEntryport,
  setExitport,
} from "@/slice/port/pickupDropSlice";
import localtourReducer, {
  setHourly,
  setPointToPoint,
  setZone,
} from "@/slice/localtour/Localslice";
import tourguideReducer, { setbookedGuide } from "@/slice/tourguide/guideslice";
import restaurantsReducer, {
  setRestaurantsService,
} from "@/slice/restaurant/RestaurantsSlice";
import dateServiceReducer, {
  setDateService,
} from "@/slice/common/dateServicesSlice";
import viewDetailsReducer from "../slice/common/ViewDetails";
import customerInfoReducer from "../slice/common/customerInfo";
import commonReducer, {
  setHaveBooking,
  setSelectedCity,
} from "../slice/common/commonSlice";
import enquiryListReducer from "../slice/common/enquiryListSlice";
import citiesReducer from "../slice/common/citiesSlice";
import bookingEnquiryReducer from "../slice/enquiries/enquiryListSlice";
import agentListReducer from "../slice/common/agentListSlice";
import convertToTourListReducer from "../slice/enquiries/enquiryToTourSlice";
import tourPackagesReducer from "../slice/tour-packages/tourPackageSlice";
import prePackagesReducer from "../slice/tour-packages/prePackagesSlice";
import profileReducer from "../slice/common/profileSlice";
import dmcReducer from "../slice/dmc/dmcSlice";
import stepperButtonReducer from "../slice/common/stepperButtonSlice";
import { loadTourSession } from "@/utils/tourSession";

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
    agentList: agentListReducer,
    convertToTourList: convertToTourListReducer,
    tourPackages: tourPackagesReducer,
    prePackages: prePackagesReducer,
    profile: profileReducer,
    dmc: dmcReducer,
    stepperButton: stepperButtonReducer,
  },
});

// Rehydrate active tour before first React render (refresh survival)
(() => {
  try {
    const saved = loadTourSession();
    if (!saved?.tourId) return;

    const { dispatch } = store;
    dispatch(setTourId1(saved.tourId));
    dispatch(setTourIdd(saved.tourId));
    dispatch(setTourId(saved.tourId));
    dispatch(setId(saved.tourId));
    if (saved.checkIn) dispatch(setCheckIn(saved.checkIn));
    if (saved.checkOut) dispatch(setCheckOut(saved.checkOut));
    dispatch(setHaveBooking(true));

    if (Array.isArray(saved.searchLocation) && saved.searchLocation.length) {
      dispatch(setSearchLocation(saved.searchLocation));
    }
    if (saved.selectedCity != null) {
      dispatch(setSelectedCity(saved.selectedCity));
    }
    if (Array.isArray(saved.cityList) && saved.cityList.length) {
      dispatch(setCity(saved.cityList));
    }
    if (saved.guests) {
      dispatch(setGuest(saved.guests));
    }
    if (saved.tourdetails || saved.checkIn || saved.checkOut) {
      dispatch(
        settourdetails({
          ...(saved.tourdetails || {}),
          CheckInTime:
            saved.tourdetails?.CheckInTime ||
            saved.tourdetails?.check_in_time ||
            saved.checkIn ||
            "",
          CheckOutTime:
            saved.tourdetails?.CheckOutTime ||
            saved.tourdetails?.check_out_time ||
            saved.checkOut ||
            "",
          destination:
            saved.tourdetails?.destination ||
            saved.tourdetails?.country ||
            saved.selectedCity ||
            undefined,
        })
      );
    }
    if (saved.searchState) {
      dispatch(updateSearchState(saved.searchState));
    }

    const svc = saved.services || {};
    if (Array.isArray(svc.hotels) && svc.hotels.length) {
      dispatch(setHotelService(svc.hotels));
    }
    if (Array.isArray(svc.attractions) && svc.attractions.length) {
      dispatch(setAttractionService(svc.attractions));
    }
    if (Array.isArray(svc.restaurants) && svc.restaurants.length) {
      dispatch(setRestaurantsService(svc.restaurants));
    }
    if (Array.isArray(svc.entryPorts) && svc.entryPorts.length) {
      dispatch(setEntryport(svc.entryPorts));
    }
    if (Array.isArray(svc.exitPorts) && svc.exitPorts.length) {
      dispatch(setExitport(svc.exitPorts));
    }
    if (Array.isArray(svc.travelPoint) && svc.travelPoint.length) {
      dispatch(setPointToPoint(svc.travelPoint));
    }
    if (Array.isArray(svc.travelHourly) && svc.travelHourly.length) {
      dispatch(setHourly(svc.travelHourly));
    }
    if (Array.isArray(svc.travelZone) && svc.travelZone.length) {
      dispatch(setZone(svc.travelZone));
    }
    if (Array.isArray(svc.guides) && svc.guides.length) {
      dispatch(setbookedGuide(svc.guides));
    }
    if (svc.dateService) {
      dispatch(setDateService(svc.dateService));
    }

    // #region agent log
    fetch("http://127.0.0.1:7539/ingest/9c7af5d8-43d0-4cfe-81fd-7c964daf146e", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Debug-Session-Id": "8029bf",
      },
      body: JSON.stringify({
        sessionId: "8029bf",
        runId: "post-fix",
        hypothesisId: "FLASH",
        location: "store.js:hydrate",
        message: "Store hydrated from tour session before render",
        data: {
          tourId: saved.tourId,
          hotelCount: Array.isArray(svc.hotels) ? svc.hotels.length : 0,
          searchLocationCount: Array.isArray(saved.searchLocation)
            ? saved.searchLocation.length
            : 0,
        },
        timestamp: Date.now(),
      }),
    }).catch(() => {});
    // #endregion
  } catch {
    // ignore hydration errors
  }
})();

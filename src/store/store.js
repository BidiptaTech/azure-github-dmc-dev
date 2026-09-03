import { configureStore } from "@reduxjs/toolkit";
import findPlaceSlice from "../features/hero/findPlaceSlice";
import attractionsReducer, {
  setAttractionService,
  setAttractionDetails,
  setSelectedAttraction,
  setSearchParams,
  setAttractionCheckoutDraft,
} from "../slice/attractions/attractionSlice";
import authReducer, { setTourIdd } from "../slice/common/authSlices";
import listsReducer from "../slice/common/TourlistSlice";
import categoryReducer, {
  setPriceMode as setCategoryPriceMode,
  setPriceModeId as setCategoryPriceModeId,
} from "../slice/hotel/CategorySlice";
import hotelsReducer, {
  setId,
  setHotelService,
  settourdetails,
  updateSearchState,
} from "../slice/hotel/hotelSlice";
import hoteldetailsReducer, {
  setHotelDetails,
  setCheckoutHotel,
  setHotelImages,
  setHotelPolicies,
} from "../slice/hotel/HotelDetailsSlice";
import loginReducer from "@/pages/login/loginSlice";
import HotelAvailabilityReducer, {
  setId as setRoomsHotelId,
  settourid as setRoomsTourId,
  setRoomDatas,
} from "../slice/hotel/HotelAvailabilitySlice";
import bookingReducer, {
  setCheckIn,
  setCheckOut,
  setSearchLocation,
  setGuest,
} from "@/slice/common/BookingSlice";
import enquiryReducer from "@/slice/common/EnquirySlice";
import cityReducer, { setCity } from "@/slice/common/citySlice";
import editingReducer, { setTourId1 } from "@/slice/common/EditSlice";
import stepsReducer, {
  setTourId,
  hydrateStepsFromSession,
} from "@/slice/common/stepsSlice";
import pickupDropReducer, {
  setEntryport,
  setExitport,
  setentrypickup as setPortEntrypickup,
  setentrydropoff as setPortEntrydropoff,
  setpickupdate as setPortPickupdate,
  setentrytime as setPortEntrytime,
  setexitpickup as setPortExitpickup,
  setexitdropoff as setPortExitdropoff,
  setexittime as setPortExittime,
  setadult as setPortAdult,
  setchildren as setPortChildren,
  settourId as setPortTourId,
  setPickupPlaceid as setPortPickupPlaceid,
  setDropoffPlaceid as setPortDropoffPlaceid,
  setPickupPlaceid1 as setPortPickupPlaceid1,
  setDropoffPlaceid1 as setPortDropoffPlaceid1,
  setMode as setPortMode,
  setPriceMode as setPortPriceMode,
  setbookingtype1 as setPortBookingtype,
  setSelectionType as setPortSelectionType,
  setPortZoneType as setPortZoneType,
  setCheckoutVehicle as setPortCheckoutVehicle,
} from "@/slice/port/pickupDropSlice";
import localtourReducer, {
  setHourly,
  setPointToPoint,
  setZone,
  setSelectionType,
  setCheckoutVehicle,
  setexitpickup,
  setpickdate,
  setentrytime as setLocalEntrytime,
  setentrytime1,
  setentrypickup as setLocalEntrypickup,
  setentrydropoff as setLocalEntrydropoff,
  setPickupPlaceid as setLocalPickupPlaceid,
  setDropoffPlaceid as setLocalDropoffPlaceid,
  setPickupZoneid,
  setDropoffZoneid,
  setMode as setLocalMode,
  setadult as setLocalAdult,
  setchildren as setLocalChildren,
  sethour as setLocalHour,
  setPriceMode1,
} from "@/slice/localtour/Localslice";
import tourguideReducer, {
  setbookedGuide,
  setentrypickup,
  setpickupdate,
  setentrytime,
  sethour,
  setHourlyPrice,
  setadult,
  setchildren,
  setPickupPlaceid,
  setDropoffPlaceid,
  setMode,
  setCheckoutGuide,
} from "@/slice/tourguide/guideslice";
import restaurantsReducer, {
  setRestaurantsService,
  setSelectedRestaurant,
  setListRestaurant,
  setSearchParams as setRestaurantSearchParams,
  setRestaurantCheckoutDraft,
} from "@/slice/restaurant/RestaurantsSlice";
import dateServiceReducer, {
  setDateService,
} from "@/slice/common/dateServicesSlice";
import viewDetailsReducer from "../slice/common/ViewDetails";
import customerInfoReducer from "../slice/common/customerInfo";
import commonReducer, {
  setHaveBooking,
  setSelectedCity,
  setBookingType,
  setBookingMode,
  setGuestCounts,
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
import cartReducer from "../slice/cart/carSlice";
import { loadTourSession } from "@/utils/tourSession";
import { resolveCountryName } from "@/utils/locationFormat";

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
    cart: cartReducer,
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
    dispatch(setHaveBooking(!!saved.haveBooking));
    if (saved.bookingType) dispatch(setBookingType(saved.bookingType));
    if (saved.bookingMode) dispatch(setBookingMode(saved.bookingMode));
    if (saved.guestCounts) dispatch(setGuestCounts(saved.guestCounts));

    dispatch(
      hydrateStepsFromSession({
        id: saved.tourId,
        stepStatus1: saved.stepStatus1,
        localStepStatus: saved.localStepStatus,
        currentStep: saved.currentStep,
        localCurrentStep: saved.localCurrentStep,
        active_status: saved.active_status,
        type: saved.stepType,
      })
    );

    if (Array.isArray(saved.searchLocation) && saved.searchLocation.length) {
      dispatch(setSearchLocation(saved.searchLocation));
    }
    if (saved.selectedCity != null) {
      dispatch(setSelectedCity(saved.selectedCity));
    }

    const countryDestination = resolveCountryName(
      (typeof saved.tourdetails?.destination === "string" &&
        saved.tourdetails.destination) ||
        (typeof saved.tourdetails?.country === "string" &&
          saved.tourdetails.country) ||
        saved.searchLocation ||
        "",
      []
    );

    if (Array.isArray(saved.cityList) && saved.cityList.length) {
      dispatch(
        setCity({
          cities: saved.cityList,
          country: countryDestination,
        })
      );
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
            countryDestination || saved.tourdetails?.destination || "",
          country: countryDestination || saved.tourdetails?.country || "",
        })
      );
    }
    if (saved.searchState) {
      dispatch(updateSearchState(saved.searchState));
    }

    const hc = saved.hotelCheckout;
    if (hc) {
      if (hc.checkoutHotel) dispatch(setCheckoutHotel(hc.checkoutHotel));
      if (hc.bookingDetails) dispatch(setHotelDetails(hc.bookingDetails));
      if (hc.hotelPolicies) dispatch(setHotelPolicies(hc.hotelPolicies));
      if (hc.images) dispatch(setHotelImages(hc.images));
      if (hc.roomDatas) dispatch(setRoomDatas(hc.roomDatas));
      if (hc.roomsId) dispatch(setRoomsHotelId(hc.roomsId));
      if (hc.roomsTourId != null) dispatch(setRoomsTourId(hc.roomsTourId));
      if (hc.priceMode != null) dispatch(setCategoryPriceMode(hc.priceMode));
      if (hc.priceModeId) dispatch(setCategoryPriceModeId(hc.priceModeId));
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

    const gc = saved.guideCheckout;
    if (gc) {
      if (gc.entrypickup) dispatch(setentrypickup(gc.entrypickup));
      if (gc.pickupdate) dispatch(setpickupdate(gc.pickupdate));
      if (gc.entrytime) dispatch(setentrytime(gc.entrytime));
      if (gc.hours) dispatch(sethour(gc.hours));
      if (gc.hourlyPrice != null) dispatch(setHourlyPrice(gc.hourlyPrice));
      if (gc.adults != null) dispatch(setadult(gc.adults));
      if (gc.children != null) dispatch(setchildren(gc.children));
      if (gc.PickupPlaceid) dispatch(setPickupPlaceid(gc.PickupPlaceid));
      if (gc.DropoffPlaceid) dispatch(setDropoffPlaceid(gc.DropoffPlaceid));
      if (gc.mode) dispatch(setMode(gc.mode));
      if (gc.checkoutGuide) dispatch(setCheckoutGuide(gc.checkoutGuide));
    }

    const ac = saved.attractionCheckout;
    if (ac) {
      if (ac.attractionDetails) dispatch(setAttractionDetails(ac.attractionDetails));
      if (ac.selectedAttraction) dispatch(setSelectedAttraction(ac.selectedAttraction));
      if (ac.searchParams) dispatch(setSearchParams(ac.searchParams));
      if (ac.checkoutDraft) dispatch(setAttractionCheckoutDraft(ac.checkoutDraft));
    }

    const rc = saved.restaurantCheckout;
    if (rc) {
      if (rc.selectedRestaurant) dispatch(setSelectedRestaurant(rc.selectedRestaurant));
      if (rc.listRestaurant) dispatch(setListRestaurant(rc.listRestaurant));
      if (rc.searchParams) dispatch(setRestaurantSearchParams(rc.searchParams));
      if (rc.checkoutDraft) dispatch(setRestaurantCheckoutDraft(rc.checkoutDraft));
    }

    const pc = saved.portCheckout;
    if (pc) {
      if (pc.selectionType !== undefined) dispatch(setPortSelectionType(pc.selectionType));
      if (pc.portZoneType !== undefined) dispatch(setPortZoneType(pc.portZoneType));
      if (pc.checkoutVehicle) dispatch(setPortCheckoutVehicle(pc.checkoutVehicle));

      if (pc.entrypickup) dispatch(setPortEntrypickup(pc.entrypickup));
      if (pc.entrydropoff) dispatch(setPortEntrydropoff(pc.entrydropoff));
      if (pc.pickupdate) dispatch(setPortPickupdate(pc.pickupdate));
      if (pc.entrytime) dispatch(setPortEntrytime(pc.entrytime));
      if (pc.PickupPlaceid) dispatch(setPortPickupPlaceid(pc.PickupPlaceid));
      if (pc.DropoffPlaceid) dispatch(setPortDropoffPlaceid(pc.DropoffPlaceid));

      if (pc.exitpickup) dispatch(setPortExitpickup(pc.exitpickup));
      if (pc.exitdropoff) dispatch(setPortExitdropoff(pc.exitdropoff));
      if (pc.exittime) dispatch(setPortExittime(pc.exittime));
      if (pc.PickupPlaceid1) dispatch(setPortPickupPlaceid1(pc.PickupPlaceid1));
      if (pc.DropoffPlaceid1) dispatch(setPortDropoffPlaceid1(pc.DropoffPlaceid1));

      if (pc.adult != null) dispatch(setPortAdult(pc.adult));
      if (pc.children != null) dispatch(setPortChildren(pc.children));
      if (pc.mode) dispatch(setPortMode(pc.mode));
      if (pc.pricemode != null) dispatch(setPortPriceMode(pc.pricemode));
      if (pc.bookingtype) dispatch(setPortBookingtype(pc.bookingtype));
    }

    const lc = saved.localTransferCheckout;
    if (lc) {
      if (lc.selectionType) dispatch(setSelectionType(lc.selectionType));
      if (lc.checkoutVehicle) dispatch(setCheckoutVehicle(lc.checkoutVehicle));
      if (lc.exitpickup) dispatch(setexitpickup(lc.exitpickup));
      if (lc.pickdate) dispatch(setpickdate(lc.pickdate));
      if (lc.entrytime) dispatch(setLocalEntrytime(lc.entrytime));
      if (lc.entrytime1) dispatch(setentrytime1(lc.entrytime1));
      if (lc.entrypickup) dispatch(setLocalEntrypickup(lc.entrypickup));
      if (lc.entrydropoff) dispatch(setLocalEntrydropoff(lc.entrydropoff));
      if (lc.PickupPlaceid) dispatch(setLocalPickupPlaceid(lc.PickupPlaceid));
      if (lc.DropoffPlaceid) dispatch(setLocalDropoffPlaceid(lc.DropoffPlaceid));
      if (lc.PickupZoneid) dispatch(setPickupZoneid(lc.PickupZoneid));
      if (lc.DropoffZoneid) dispatch(setDropoffZoneid(lc.DropoffZoneid));
      if (lc.mode) dispatch(setLocalMode(lc.mode));
      if (lc.adult != null) dispatch(setLocalAdult(lc.adult));
      if (lc.children != null) dispatch(setLocalChildren(lc.children));
      if (lc.hours) dispatch(setLocalHour(lc.hours));
      if (lc.pricemode) dispatch(setPriceMode1(lc.pricemode));
    }
  } catch {
    // ignore hydration errors
  }
})();

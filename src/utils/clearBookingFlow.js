import { clearTourSession } from "@/utils/tourSession";
import { setTourIdd } from "@/slice/common/authSlices";
import { setTourId, resetSteps } from "@/slice/common/stepsSlice";
import { setTourId1 } from "@/slice/common/EditSlice";
import {
  setId,
  setHotelService,
  settourdetails,
  resetHotels,
} from "@/slice/hotel/hotelSlice";
import { setAttractionService, clearAttractions } from "@/slice/attractions/attractionSlice";
import {
  setRestaurantsService,
  clearRestaurants,
} from "@/slice/restaurant/RestaurantsSlice";
import {
  setEntryport,
  setExitport,
  resetVehicles,
  setSelectedPort,
} from "@/slice/port/pickupDropSlice";
import {
  setHourly,
  setPointToPoint,
  setZone,
  resetVehicles1,
} from "@/slice/localtour/Localslice";
import { setbookedGuide, resetguide } from "@/slice/tourguide/guideslice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { setHaveBooking, setSelectedCity, setBookingType, setBookingMode } from "@/slice/common/commonSlice";
import { clearUserInfo } from "@/slice/common/customerInfo";
import { resetAllServiceResponses } from "@/slice/common/stepperButtonSlice";

/**
 * Exit booking flow: remove sessionStorage tour data and clear
 * booking-related Redux so no stale tour remains.
 */
export function clearBookingFlow(dispatch) {
  if (!dispatch) return;

  clearTourSession();

  dispatch(setTourIdd(null));
  dispatch(setTourId(null));
  dispatch(setTourId1(null));
  dispatch(setId(0));
  dispatch(resetSteps());

  dispatch(setHaveBooking(false));
  dispatch(setSelectedCity(null));
  dispatch(setBookingType(null));
  dispatch(setBookingMode("dmc"));
  dispatch(clearUserInfo());
  dispatch(resetAllServiceResponses());

  dispatch(setHotelService([]));
  dispatch(resetHotels());
  dispatch(settourdetails([]));

  dispatch(clearAttractions());
  dispatch(setAttractionService([]));

  dispatch(clearRestaurants());
  dispatch(setRestaurantsService([]));

  dispatch(setEntryport([]));
  dispatch(setExitport([]));
  dispatch(resetVehicles());
  dispatch(setSelectedPort(""));

  dispatch(setPointToPoint([]));
  dispatch(setHourly([]));
  dispatch(setZone([]));
  dispatch(resetVehicles1());

  dispatch(setbookedGuide([]));
  dispatch(resetguide());

  dispatch(setDateService([]));
}

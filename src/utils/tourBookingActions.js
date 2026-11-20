import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import swal from "sweetalert";
import {
  setCheckIn,
  setCheckOut,
  setSearchLocation,
} from "@/slice/common/BookingSlice";
import {
  resetHotels,
  setId,
  updateSearchState,
  settourdetails,
} from "@/slice/hotel/hotelSlice";
import {
  setTourId,
  statusUpdate,
  setType,
  updateStepStatus,
} from "@/slice/common/stepsSlice";
import { setTourIdd } from "@/slice/common/authSlices";
import { fetchEditid, setTourId1, deleteTour } from "@/slice/common/EditSlice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { fetchLists, setTourType } from "@/slice/common/TourlistSlice";
import { fetchViewDetails } from "@/slice/common/ViewDetails";
import {
  setUserInfo as customerInfoSetUserInfo,
  resetBookingState,
  clearUserInfo,
} from "@/slice/common/customerInfo";
import { clearCart, setTripCustomerInfo } from "@/slice/cart/carSlice";
import {
  setBookingType,
  setHaveBooking,
  setCityWiseDates,
  clearCityWiseDates,
  setSelectedCity,
} from "@/slice/common/commonSlice";
import { clearAttractions } from "@/slice/attractions/attractionSlice";
import { clearRestaurants } from "@/slice/restaurant/RestaurantsSlice";
import {
  UpdateCustomPackage,
  clearAllServices,
} from "@/slice/tour-packages/tourPackageSlice";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import { setSelectedDmcId } from "@/slice/dmc/dmcSlice";
import { resetAllServiceResponses } from "@/slice/common/stepperButtonSlice";
import { resetguide } from "@/slice/tourguide/guideslice";
import { resetVehicles1, setSelectedPort } from "@/slice/localtour/Localslice";
import { resetVehicles } from "@/slice/port/pickupDropSlice";
import { appPath } from "@/utils/appPaths";

dayjs.extend(customParseFormat);

export function tourEditSteps(tourId) {
  return [
    { key: "hotel", path: `/app/view-hotel-search/${tourId}` },
    { key: "port", path: "/app/pickupdrop" },
    { key: "attraction", path: "/app/attractions" },
    { key: "guide", path: "/app/tourguide" },
    { key: "restaurent", path: "/app/restaurants" },
    { key: "travel", path: "/app/localtransfer" },
  ];
}

function formatDate1(dateString) {
  if (!dateString) return null;
  if (String(dateString).includes("-") && !String(dateString).includes("/")) {
    const parsed = dayjs(dateString, ["YYYY-MM-DD", "DD-MM-YYYY"], true);
    if (parsed.isValid()) return parsed.format("DD/MM/YYYY");
  }
  const [day, month, year] = String(dateString).split("/");
  if (!day || !month || !year) return dateString;
  return `${String(day).padStart(2, "0")}/${String(month).padStart(2, "0")}/${year}`;
}

export function isTourCancelled(tour) {
  const status = (tour?.tour_status || "").toLowerCase();
  return (
    status.startsWith("cancel") ||
    status.startsWith("refund") ||
    status.startsWith("refunded") ||
    status.startsWith("auto cancel")
  );
}

export function canAddMoreServices(tour) {
  return Boolean(tour?.id) && tour.editOff !== 1 && !isTourCancelled(tour);
}

export function canUpdateTourPlan(tour) {
  return Boolean(tour?.id) && !isTourCancelled(tour);
}

export function canCancelTour(tour) {
  return Boolean(tour?.id) && !isTourCancelled(tour);
}

export function canNegotiate(tour, userRole) {
  return (
    tour?.booking_type === "enquiry" &&
    userRole === "Agent" &&
    !isTourCancelled(tour)
  );
}

function buildCountryMappings(userCountry) {
  const nameToCode = {};
  const codeToName = {};
  if (Array.isArray(userCountry)) {
    userCountry.forEach((country) => {
      if (country?.name && country?.code) {
        nameToCode[country.name] = country.code;
        nameToCode[country.name.toLowerCase()] = country.code;
        codeToName[country.code] = country.name;
        codeToName[country.code.toLowerCase()] = country.name;
      }
    });
  }
  return { nameToCode, codeToName };
}

/**
 * Add more services — same flow as dashboard Pending/Upcoming handleEdit.
 */
export async function startAddMoreServices({
  tour,
  dispatch,
  navigate,
  userCountry,
}) {
  if (!tour?.id) throw new Error("Missing tour id");

  const countryMappings = buildCountryMappings(userCountry);
  const formattedCheckIn = formatDate1(tour.check_in_time);
  const formattedCheckOut = formatDate1(tour.check_out_time);

  dispatch(setHaveBooking(false));
  dispatch(clearAttractions());
  dispatch(clearRestaurants());
  dispatch(clearCityWiseDates());
  dispatch(clearCart());
  dispatch(resetAllServiceResponses());
  dispatch(setId(tour.id));
  dispatch(setTourId(tour.id));
  dispatch(setTourId1(tour.id));
  dispatch(setTourIdd(tour.id));
  dispatch(resetBookingState());
  dispatch(clearUserInfo());
  if (formattedCheckIn) dispatch(setCheckIn(formattedCheckIn));
  if (formattedCheckOut) dispatch(setCheckOut(formattedCheckOut));
  dispatch(fetchViewDetails({ tour_id: tour.id }));
  dispatch(resetguide());
  dispatch(resetVehicles());
  dispatch(resetVehicles1());
  dispatch(setSelectedPort(""));

  const response = await dispatch(fetchEditid()).unwrap();
  const data = response.data;
  const bookingType = data.bookingType || "enquiry";
  dispatch(setBookingType(bookingType));

  if (data.customerInfo && typeof data.customerInfo === "object") {
    if (data.customerInfo.fullName) {
      dispatch(setHaveBooking(true));
    }
    const cartCustomerInfo = {
      fullName: data.customerInfo.fullName || "",
      email: data.customerInfo.email || "",
      phone: data.customerInfo.phone || "",
      countryCode:
        data.customerInfo.countryCode || data.customerInfo.country_code || "",
      address1: data.customerInfo.address1 || "",
      address2: data.customerInfo.address2 || "",
      state: data.customerInfo.state || "",
      zip: data.customerInfo.zip || "",
      specialRequests:
        data.customerInfo.specialRequest ||
        data.customerInfo.specialRequests ||
        "",
    };
    dispatch(customerInfoSetUserInfo(cartCustomerInfo));
    dispatch(
      setTripCustomerInfo({
        tourId: data.tour_id,
        customerInfo: cartCustomerInfo,
        tourDetails: {
          ...data,
          check_in:
            data.CheckInTime || data.check_in || tour.check_in_time,
          check_out:
            data.CheckOutTime || data.check_out || tour.check_out_time,
          tour_id: data.tour_id,
        },
      })
    );
  }

  const id = data.tour_id;
  const destination = data.destination;
  if (Array.isArray(data.cityWiseDates)) {
    dispatch(setCityWiseDates(data.cityWiseDates));
  }
  if (!id || !destination) {
    throw new Error("Invalid response data.");
  }

  if (data.service?.date_service) {
    dispatch(setDateService(data.service.date_service));
  }

  if (data.step) {
    dispatch(updateStepStatus({ key: data.step, status: 2 }));
    dispatch(setType(null));
    const matchedStep = tourEditSteps(id).find((step) => step.key === data.step);
    if (matchedStep) {
      navigate(matchedStep.path, { state: { tourDetails: data } });
      dispatch(statusUpdate()).unwrap().catch(() => {});
    }
  }

  dispatch(setTourId(id));

  const destinationArray = Array.isArray(destination)
    ? destination
    : String(destination)
        .split(",")
        .map((name) => name.trim());

  const countryCodeArray = destinationArray
    .map((item) => {
      let code = countryMappings.nameToCode[item];
      if (!code) {
        const name = countryMappings.codeToName[item];
        if (name) code = countryMappings.nameToCode[name];
      }
      return code;
    })
    .filter(Boolean);

  if (countryCodeArray.length) {
    dispatch(setSearchLocation(countryCodeArray));
  } else if (data.short_code) {
    dispatch(setSearchLocation(data.short_code));
  }

  const destinationNames = destinationArray
    .map((item) => countryMappings.codeToName[item] || item)
    .filter(Boolean);

  const checkInForSearch =
    data.CheckInTime
      ? dayjs(data.CheckInTime, "DD/MM/YYYY").format("YYYY-MM-DD")
      : formattedCheckIn
        ? dayjs(formattedCheckIn, "DD/MM/YYYY").format("YYYY-MM-DD")
        : null;
  const checkOutForSearch =
    data.CheckOutTime
      ? dayjs(data.CheckOutTime, "DD/MM/YYYY").format("YYYY-MM-DD")
      : formattedCheckOut
        ? dayjs(formattedCheckOut, "DD/MM/YYYY").format("YYYY-MM-DD")
        : null;

  dispatch(
    updateSearchState({
      location: destinationNames.length ? destinationNames : destinationArray,
      ucheckIn: checkInForSearch,
      ucheckOut: checkOutForSearch,
    })
  );

  if (destinationNames.length > 0) {
    dispatch(setSelectedCity(destinationNames[0]));
  }

  dispatch(resetHotels());
  dispatch(
    settourdetails({
      ...data,
      country: destination,
      service_details: {
        ...data.service,
        country: destination,
      },
    })
  );

  return data;
}

/**
 * Update tour plan — opens package builder for the tour.
 */
export async function startUpdateTourPlan({ tour, dispatch, navigate }) {
  if (!tour?.id) throw new Error("Missing tour id");

  dispatch(clearAllServices());
  dispatch(clearAttractions());
  dispatch(clearRestaurants());
  dispatch(resetVehicles());
  dispatch(resetVehicles1());
  dispatch(resetguide());

  const response = await dispatch(
    UpdateCustomPackage({ tour_id: tour.id })
  ).unwrap();

  dispatch(
    setSelectedDmcId({
      dmcId: response.dmc_id,
      dmcData: {
        originalData: {
          price_hide: response.price_hide,
          zone_on: response.zone_on,
        },
      },
    })
  );
  dispatch(resetPackages());
  // Normal booking update — Plan a trip builder, not Packages
  navigate(appPath("plan"), {
    state: { startTrip: true, from: "bookings" },
  });
  return response;
}

/**
 * Cancel / delete tour with confirmation.
 */
export async function cancelTourBooking({ tour, dispatch, listType }) {
  if (!tour?.id) throw new Error("Missing tour id");

  const willDelete = await swal({
    title: "Are you sure to delete this tour?",
    icon: "warning",
    buttons: ["Cancel", "Delete"],
    dangerMode: true,
  });

  if (!willDelete) return false;

  await dispatch(deleteTour(tour.id)).unwrap();
  if (listType) {
    dispatch(setTourType(listType));
    await dispatch(fetchLists({ reset: true, type: listType })).unwrap();
  } else {
    await dispatch(fetchLists({ reset: true })).unwrap();
  }
  await swal("Deleted!", "Your tour has been deleted!", "success");
  return true;
}

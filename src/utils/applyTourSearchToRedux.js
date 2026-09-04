import moment from "moment";
import {
  setCheckIn,
  setCheckOut,
  setGuest,
} from "@/slice/common/BookingSlice";
import {
  setId,
  updateSearchState,
  settourdetails,
} from "@/slice/hotel/hotelSlice";
import {
  setSelectedCity,
  setCityWiseDates,
} from "@/slice/common/commonSlice";
import { setCity } from "@/slice/common/citySlice";

/**
 * Normalize destination into [{ city, country }, ...] — same shape as
 * MainFilterSearchBox handleSearch → settourdetails / updateSearchState.
 */
export const normalizeDestinationLocations = (destination) => {
  if (destination == null || destination === "") return [];

  if (Array.isArray(destination)) {
    return destination
      .map((entry) => {
        if (entry == null) return null;
        if (typeof entry === "string") {
          return { city: entry, country: "" };
        }
        if (typeof entry === "object") {
          const city = entry.city || entry.name || "";
          if (!city) return null;
          return { city, country: entry.country || "" };
        }
        return { city: String(entry), country: "" };
      })
      .filter(Boolean);
  }

  if (typeof destination === "string") {
    return [{ city: destination, country: "" }];
  }

  if (typeof destination === "object") {
    const city = destination.city || destination.name || "";
    return city
      ? [{ city, country: destination.country || "" }]
      : [];
  }

  return [];
};

export const getDestinationCityNames = (destination) =>
  normalizeDestinationLocations(destination)
    .map((loc) => loc.city)
    .filter(Boolean);

export const formatDestinationLabel = (destination) => {
  const names = getDestinationCityNames(destination);
  if (names.length) return names.join(", ");
  if (typeof destination === "string") return destination;
  return "—";
};

/**
 * Apply the same tour / search Redux fields that handleSearch writes
 * after validation (dates, guests, destinations, cityWiseDates, tour meta).
 *
 * Used by hero search and cart edit / checkout restore.
 */
export const applyTourSearchToRedux = (dispatch, payload = {}) => {
  if (!dispatch) return;

  const destinationLocations = normalizeDestinationLocations(
    payload.destination ?? payload.destinationLocations
  );
  const cityWiseDates = Array.isArray(payload.cityWiseDates)
    ? payload.cityWiseDates
    : [];
  const checkIn =
    payload.checkIn ||
    payload.check_in ||
    payload.CheckInTime ||
    "";
  const checkOut =
    payload.checkOut ||
    payload.check_out ||
    payload.CheckOutTime ||
    "";
  const adults = Number(
    payload.adults ?? payload.adult ?? payload.Adults ?? 1
  );
  const children = Number(
    payload.children ?? payload.child ?? payload.Children ?? 0
  );
  const infant = Number(
    payload.infant ?? payload.infants ?? payload.Infants ?? 0
  );
  const adultGenders = Array.isArray(payload.adultGenders)
    ? payload.adultGenders
    : undefined;
  const childrenAges = Array.isArray(payload.childrenAges)
    ? payload.childrenAges
    : undefined;
  const tourId =
    payload.tour_id ?? payload.tourId ?? payload.id ?? null;

  const destinationNames = destinationLocations.map((loc) => loc.city);
  const primaryCountry =
    payload.country ||
    destinationLocations.find((loc) => loc.country)?.country ||
    "";

  const ucheckIn = checkIn
    ? moment(checkIn, "DD/MM/YYYY").format("YYYY-MM-DD")
    : "";
  const ucheckOut = checkOut
    ? moment(checkOut, "DD/MM/YYYY").format("YYYY-MM-DD")
    : "";

  // Mirror MainFilterSearchBox handleSearch data writes (order preserved)
  dispatch(setCityWiseDates(cityWiseDates));
  dispatch(setCheckIn(checkIn));
  dispatch(setCheckOut(checkOut));

  const guestPayload = {
    adults,
    children,
    infant,
  };
  if (adultGenders) guestPayload.adultGenders = adultGenders;
  if (childrenAges) guestPayload.childrenAges = childrenAges;
  dispatch(setGuest(guestPayload));

  dispatch(
    updateSearchState({
      location: destinationLocations,
      cityWiseDates,
      ucheckIn,
      ucheckOut,
      guests: {
        adults,
        children,
        infant,
      },
    })
  );

  dispatch(
    settourdetails({
      destination: destinationLocations,
      cityWiseDates,
      adult: adults,
      child: children,
      infant,
      CheckInTime: checkIn,
      CheckOutTime: checkOut,
      tour_id: tourId,
      country: primaryCountry,
      ...(adultGenders ? { adultGenders } : {}),
      ...(childrenAges ? { childrenAges } : {}),
    })
  );

  dispatch(setId(tourId || 0));

  dispatch(
    setCity({
      cities: destinationNames,
      country: primaryCountry,
    })
  );
  dispatch(setSelectedCity(null));
};

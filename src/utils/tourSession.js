const TOUR_SESSION_KEY = "activeTourContext";

function safeClone(value, fallback) {
  try {
    return JSON.parse(JSON.stringify(value));
  } catch {
    return fallback;
  }
}

function writeTourSession(payload) {
  if (typeof sessionStorage === "undefined") return;
  sessionStorage.setItem(TOUR_SESSION_KEY, JSON.stringify(payload));
}

/**
 * Persist the active tour booking context into sessionStorage.
 * Overwrites any previous active tour (one booking at a time).
 */
export function saveTourSession(context) {
  if (typeof sessionStorage === "undefined") return false;
  if (!context?.tourId) return false;

  const base = {
    tourId: context.tourId,
    // bookings slice
    checkIn: context.checkIn || "",
    checkOut: context.checkOut || "",
    searchLocation: safeClone(context.searchLocation || [], []),
    guests: safeClone(context.guests || null, null),
    destination: safeClone(context.destination || [], []),
    // common slice
    haveBooking: !!context.haveBooking,
    selectedCity: safeClone(context.selectedCity ?? null, null),
    bookingType: context.bookingType ?? null,
    bookingMode: context.bookingMode ?? null,
    guestCounts: safeClone(context.guestCounts || null, null),
    // steps slice
    stepStatus1: safeClone(context.stepStatus1 || null, null),
    localStepStatus: safeClone(context.localStepStatus || null, null),
    currentStep: context.currentStep ?? null,
    localCurrentStep: context.localCurrentStep ?? null,
    active_status: context.active_status ?? null,
    stepType: context.stepType ?? null,
    // hotel / city helpers
    cityList: safeClone(
      (Array.isArray(context.cityList) ? context.cityList : [])
        .map((c) =>
          typeof c === "string"
            ? c
            : c?.address || c?.name || c?.city || ""
        )
        .filter(Boolean),
      []
    ),
    tourdetails: safeClone(context.tourdetails || null, null),
    searchState: safeClone(context.searchState || null, null),
    hotelCheckout: safeClone(context.hotelCheckout || null, null),
    // Guide activity-single checkout draft (survives refresh)
    guideCheckout: safeClone(context.guideCheckout || null, null),
    // Attraction tour-single checkout draft (survives refresh)
    attractionCheckout: safeClone(context.attractionCheckout || null, null),
    // Restaurant details checkout draft (survives refresh)
    restaurantCheckout: safeClone(context.restaurantCheckout || null, null),
    // Port (entry/exit port, zone mode) checkout draft (survives refresh)
    portCheckout: safeClone(context.portCheckout || null, null),
    localTransferCheckout: safeClone(context.localTransferCheckout || null, null),
    services: {},
  };

  const incoming = context.services || {};
  const serviceKeys = [
    "hotels",
    "attractions",
    "restaurants",
    "entryPorts",
    "exitPorts",
    "travelPoint",
    "travelHourly",
    "travelZone",
    "guides",
    "dateService",
  ];

  serviceKeys.forEach((key) => {
    const cloned = safeClone(incoming[key], null);
    if (cloned != null) {
      base.services[key] = cloned;
    }
  });

  try {
    writeTourSession(base);
    return true;
  } catch {
    try {
      writeTourSession({ ...base, services: {} });
      return false;
    } catch {
      return false;
    }
  }
}

/**
 * Load the active tour booking from sessionStorage (refresh restore).
 */
export function loadTourSession() {
  if (typeof sessionStorage === "undefined") return null;
  try {
    const raw = sessionStorage.getItem(TOUR_SESSION_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (!parsed?.tourId) return null;
    return parsed;
  } catch {
    return null;
  }
}

/**
 * Remove active tour booking from sessionStorage (exit booking flow).
 */
export function clearTourSession() {
  try {
    if (typeof sessionStorage !== "undefined") {
      sessionStorage.removeItem(TOUR_SESSION_KEY);
    }
  } catch {
    // ignore
  }
}

export function hasTourSession() {
  return !!loadTourSession()?.tourId;
}

export { TOUR_SESSION_KEY };

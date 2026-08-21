const TOUR_SESSION_KEY = "activeTourContext";

function safeClone(value, fallback) {
  try {
    return JSON.parse(JSON.stringify(value));
  } catch {
    return fallback;
  }
}

function writeTourSession(payload) {
  const raw = JSON.stringify(payload);
  sessionStorage.setItem(TOUR_SESSION_KEY, raw);
  try {
    localStorage.setItem(TOUR_SESSION_KEY, raw);
  } catch {
    // ignore localStorage failures
  }
}

export function saveTourSession(context) {
  if (typeof sessionStorage === "undefined") return false;
  if (!context?.tourId) return false;

  const base = {
    tourId: context.tourId,
    checkIn: context.checkIn || "",
    checkOut: context.checkOut || "",
    haveBooking: !!context.haveBooking,
    searchLocation: safeClone(context.searchLocation || [], []),
    selectedCity: safeClone(context.selectedCity ?? null, null),
    // Persist city list as plain strings only
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
    guests: safeClone(context.guests || null, null),
    tourdetails: safeClone(context.tourdetails || null, null),
    searchState: safeClone(context.searchState || null, null),
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
    // Last resort: persist identity + search without services
    try {
      writeTourSession({ ...base, services: {} });
      return false;
    } catch {
      return false;
    }
  }
}

export function loadTourSession() {
  if (typeof sessionStorage === "undefined") return null;
  try {
    const raw =
      sessionStorage.getItem(TOUR_SESSION_KEY) ||
      localStorage.getItem(TOUR_SESSION_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (!parsed?.tourId) return null;
    return parsed;
  } catch {
    return null;
  }
}

export function clearTourSession() {
  try {
    sessionStorage.removeItem(TOUR_SESSION_KEY);
  } catch {
    // ignore
  }
  try {
    localStorage.removeItem(TOUR_SESSION_KEY);
  } catch {
    // ignore
  }
}

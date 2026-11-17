import { DateObject } from "react-multi-date-picker";
import { toCityOnly } from "@/utils/locationFormat";

export const toDateObject = (value) => {
  if (!value) return null;
  if (value instanceof DateObject) return value;
  if (typeof value === "string" && value.includes("/")) {
    return new DateObject({ date: value, format: "DD/MM/YYYY" });
  }
  if (typeof value === "string" && value.includes("-")) {
    return new DateObject({ date: value, format: "YYYY-MM-DD" });
  }
  try {
    return new DateObject(value);
  } catch {
    return null;
  }
};

export const normalizeYmd = (value) => {
  if (!value) return null;
  if (value instanceof DateObject) return value.format("YYYY-MM-DD");
  const raw = String(value).trim();
  if (raw.includes("/")) {
    const [day, month, year] = raw.split("/");
    return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
  }
  return raw;
};

export const createStayKey = (cityIdOrName, index = 0, isReturn = false) => {
  const base = String(cityIdOrName || "city")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-");
  return `${base}-${index}${isReturn ? "-return" : ""}`;
};

export const normalizeCityWiseDatesList = (list) => {
  if (!Array.isArray(list) || list.length === 0) return [];
  const normalized = list
    .map((item, index) => {
      if (!item) return null;
      const city =
        typeof item === "string"
          ? item.trim()
          : String(item.city || "").trim();
      if (!city) return null;
      const isReturn = Boolean(item.isReturn);
      return {
        city,
        cityName: toCityOnly(city),
        checkIn: item.checkIn || item.checkin || "",
        checkOut: item.checkOut || item.checkout || "",
        isReturn,
        stayKey:
          item.stayKey ||
          createStayKey(item.city_id || city, index, isReturn),
        sourceStayKey: item.sourceStayKey || null,
        city_id: item.city_id || item.cityId || null,
        country: item.country || null,
      };
    })
    .filter(Boolean);

  // Link return rows to the nearest prior same-city main stay
  return normalized.map((stay, index) => {
    if (!stay.isReturn || stay.sourceStayKey) return stay;
    for (let i = index - 1; i >= 0; i -= 1) {
      const candidate = normalized[i];
      if (
        !candidate.isReturn &&
        toCityOnly(candidate.city).toLowerCase() ===
          toCityOnly(stay.city).toLowerCase()
      ) {
        return { ...stay, sourceStayKey: candidate.stayKey };
      }
    }
    return stay;
  });
};

export const staysAllDated = (stays) =>
  Array.isArray(stays) &&
  stays.length > 0 &&
  stays.every((stay) => Boolean(stay?.checkIn) && Boolean(stay?.checkOut));

/**
 * Resolve a city stay entry.
 * Prefer stayKey, then date-window overlap, then nth same-city match, then first name match.
 */
export const findCityDatesEntry = (cityWiseDates, cityName, opts = {}) => {
  if (!Array.isArray(cityWiseDates) || !cityWiseDates.length) {
    return null;
  }

  const { stayKey, serviceDate, occurrence = 0 } = opts;

  if (stayKey) {
    const byKey = cityWiseDates.find((entry) => entry.stayKey === stayKey);
    if (byKey) return byKey;
  }

  if (!cityName) return null;
  const needle = toCityOnly(cityName).toLowerCase();
  const matches = cityWiseDates.filter(
    (entry) => toCityOnly(entry.city).toLowerCase() === needle
  );
  if (!matches.length) return null;

  const ymd = serviceDate ? normalizeYmd(serviceDate) : null;
  if (ymd) {
    const inWindow = matches.find((entry) => {
      const cityIn = normalizeYmd(entry.checkIn);
      const cityOut = normalizeYmd(entry.checkOut);
      return cityIn && cityOut && ymd >= cityIn && ymd <= cityOut;
    });
    if (inWindow) return inWindow;
  }

  if (occurrence > 0 && matches[occurrence]) {
    return matches[occurrence];
  }

  return matches[0];
};

export const formatChipDates = (entry) => {
  if (!entry?.checkIn || !entry?.checkOut) return "";
  const start = toDateObject(entry.checkIn);
  const end = toDateObject(entry.checkOut);
  if (!start || !end) return "";
  return `${start.format("MMM DD")} – ${end.format("MMM DD")}`;
};

export const buildLocationFromCityName = (cityName, cityList = []) => {
  const name = toCityOnly(cityName);
  if (!name) return null;

  const list = Array.isArray(cityList) ? cityList : [];
  const match = list
    .map((city, index) => {
      const address =
        typeof city === "string"
          ? city
          : city?.address || city?.name || city?.city || "";
      if (!address) return null;
      return {
        id: index + 1,
        name: String(address).split(",")[0].trim(),
        address: String(address),
      };
    })
    .filter(Boolean)
    .find(
      (item) =>
        item.name.toLowerCase() === name.toLowerCase() ||
        item.address.toLowerCase().includes(name.toLowerCase())
    );

  return (
    match || {
      id: 0,
      name,
      address: String(cityName),
    }
  );
};

export const getCityDateBounds = (
  cityWiseDates,
  cityName,
  tourCheckIn,
  tourCheckOut,
  opts = {}
) => {
  const entry = findCityDatesEntry(cityWiseDates, cityName, opts);
  if (entry?.checkIn && entry?.checkOut) {
    return {
      checkIn: toDateObject(entry.checkIn),
      checkOut: toDateObject(entry.checkOut),
      entry,
    };
  }
  return {
    checkIn: tourCheckIn ? toDateObject(tourCheckIn) : null,
    checkOut: tourCheckOut ? toDateObject(tourCheckOut) : null,
    entry: null,
  };
};

const extractBookingCity = (booking) => {
  const locationRaw =
    booking?.city ||
    booking?.entrypickup ||
    booking?.userInfo?.city ||
    booking?.hotelDetails?.city ||
    booking?.hotelDetails?.location ||
    booking?.service_details?.city ||
    booking?.attractionDetails?.location ||
    booking?.restaurantDetails?.city ||
    booking?.location ||
    "";
  if (Array.isArray(locationRaw)) {
    return toCityOnly(locationRaw[0]);
  }
  return toCityOnly(locationRaw);
};

const extractBookingDate = (booking) => {
  if (booking?.bookingDate) {
    return normalizeYmd(booking.bookingDate);
  }
  const range = booking?.bookingDate;
  if (Array.isArray(range) && range[0]) {
    return normalizeYmd(range[0]);
  }
  return normalizeYmd(
    booking?.checkIn ||
      booking?.check_in ||
      booking?.selectedDate ||
      booking?.date
  );
};

export const isCityServiceBooked = (cityEntry, bookings) => {
  if (!cityEntry || !Array.isArray(bookings) || !bookings.length) {
    return false;
  }

  const cityName = toCityOnly(cityEntry.city).toLowerCase();
  const cityIn = normalizeYmd(cityEntry.checkIn);
  const cityOut = normalizeYmd(cityEntry.checkOut);

  return bookings.some((booking) => {
    const bookingCity = extractBookingCity(booking).toLowerCase();
    const bookingDate = extractBookingDate(booking);

    const cityMatch = cityName && bookingCity && bookingCity === cityName;
    const dateMatch =
      cityIn &&
      bookingDate &&
      cityOut &&
      bookingDate >= cityIn &&
      bookingDate <= cityOut;

    return cityMatch || dateMatch;
  });
};

export const buildCityChipItems = ({
  cityWiseDates,
  activeCityName,
  bookings,
  activeStayKey,
}) => {
  if (!Array.isArray(cityWiseDates) || !cityWiseDates.length) return [];

  const active = toCityOnly(activeCityName).toLowerCase();
  return cityWiseDates.map((entry) => ({
    ...entry,
    booked: isCityServiceBooked(entry, bookings),
    active: activeStayKey
      ? entry.stayKey === activeStayKey
      : toCityOnly(entry.city).toLowerCase() === active,
  }));
};

const sameCity = (a, b) => {
  if (!a || !b) return false;
  if (a.city_id && b.city_id) {
    return String(a.city_id) === String(b.city_id);
  }
  const aName = toCityOnly(a.cityName || a.city || a.name || "").toLowerCase();
  const bName = toCityOnly(b.cityName || b.city || b.name || "").toLowerCase();
  return Boolean(aName) && aName === bName;
};

/**
 * Sync base city list into stay rows while preserving return stays and dates.
 * Revisit cities (e.g. Singapore → Batam → Singapore) become return stays.
 * Explicit Return toggles are appended at the end (after later cities), never
 * as Singapore → Singapore immediately after the first visit.
 */
export const syncStaysFromCities = (cities = [], previousStays = []) => {
  const prev = Array.isArray(previousStays) ? previousStays : [];
  const next = [];
  const pendingReturns = [];
  const usedPrevKeys = new Set();

  cities.forEach((cityItem, index) => {
    const cityName = cityItem?.city || cityItem?.name || "";
    const cityId = cityItem?.city_id || cityItem?.cityId || null;
    const country = cityItem?.country || null;
    if (!cityName) return;

    const isRevisit = cities
      .slice(0, index)
      .some((earlier) => sameCity(earlier, cityItem));

    // Prefer matching unused prev stay in order (supports duplicate cities)
    const existing =
      prev.find(
        (stay) =>
          !usedPrevKeys.has(stay.stayKey) &&
          Boolean(stay.isReturn) === isRevisit &&
          sameCity(stay, { city: cityName, city_id: cityId, cityName })
      ) ||
      prev.find(
        (stay) =>
          !usedPrevKeys.has(stay.stayKey) &&
          sameCity(stay, { city: cityName, city_id: cityId, cityName })
      ) ||
      null;

    if (existing?.stayKey) usedPrevKeys.add(existing.stayKey);

    const firstVisit =
      next.find(
        (stay) =>
          !stay.isReturn &&
          sameCity(stay, { city: cityName, city_id: cityId, cityName })
      ) || null;

    const stayKey =
      existing?.stayKey ||
      createStayKey(cityId || cityName, index, isRevisit);

    next.push({
      stayKey,
      city: country ? `${cityName} (${country})` : cityName,
      cityName,
      city_id: cityId,
      country,
      checkIn: existing?.checkIn || "",
      checkOut: existing?.checkOut || "",
      isReturn: Boolean(isRevisit || existing?.isReturn),
      sourceStayKey: isRevisit
        ? firstVisit?.stayKey || existing?.sourceStayKey || null
        : null,
    });
  });

  // Keep explicit Return toggles (not already represented as a city revisit)
  prev.forEach((stay) => {
    if (!stay.isReturn || usedPrevKeys.has(stay.stayKey)) return;
    const alreadyInList = next.some(
      (item) =>
        item.stayKey === stay.stayKey ||
        (item.isReturn && item.sourceStayKey === stay.sourceStayKey)
    );
    if (alreadyInList) return;
    const sourceStillExists = next.some(
      (item) => !item.isReturn && item.stayKey === stay.sourceStayKey
    );
    if (!sourceStillExists) return;
    pendingReturns.push({ ...stay, isReturn: true });
  });

  return [...next, ...pendingReturns];
};

export const toggleReturnStay = (stays, stayKey, enabled) => {
  const list = Array.isArray(stays) ? [...stays] : [];
  const index = list.findIndex((stay) => stay.stayKey === stayKey);
  if (index < 0) return list;

  const source = list[index];
  if (source.isReturn) return list;

  const existingReturnIndex = list.findIndex(
    (stay) => stay.isReturn && stay.sourceStayKey === stayKey
  );

  if (enabled) {
    if (existingReturnIndex >= 0) return list;
    // Only allow return when a later different city exists
    // (Batam → Singapore, not Singapore → Singapore)
    const hasLaterDifferentCity = list
      .slice(index + 1)
      .some((stay) => !stay.isReturn && !sameCity(stay, source));
    if (!hasLaterDifferentCity) return list;

    const returnStay = {
      stayKey: createStayKey(
        source.city_id || source.cityName || source.city,
        index,
        true
      ),
      city: source.city,
      cityName: source.cityName || toCityOnly(source.city),
      city_id: source.city_id || null,
      country: source.country || null,
      checkIn: "",
      checkOut: "",
      isReturn: true,
      sourceStayKey: stayKey,
    };
    // Append after later cities: Singapore → Batam → Singapore (return)
    list.push(returnStay);
    return list;
  }

  if (existingReturnIndex >= 0) {
    list.splice(existingReturnIndex, 1);
  }
  return list;
};

/** Previous non-empty stay city label for route context (From → To). */
export const getPreviousStayCity = (stays, stayKey) => {
  const list = Array.isArray(stays) ? stays : [];
  const index = list.findIndex((stay) => stay.stayKey === stayKey);
  if (index <= 0) return null;
  const prev = list[index - 1];
  if (!prev) return null;
  return prev.cityName || toCityOnly(prev.city) || prev.city || null;
};

export const updateStayDates = (stays, stayKey, checkIn, checkOut) =>
  (Array.isArray(stays) ? stays : []).map((stay) =>
    stay.stayKey === stayKey
      ? {
          ...stay,
          checkIn: checkIn || "",
          checkOut: checkOut || "",
        }
      : stay
  );

export const toCityWiseDatesPayload = (stays) =>
  (Array.isArray(stays) ? stays : [])
    .map((item) => ({
      city: item.cityName || toCityOnly(item.city) || item.city,
      checkIn: item.checkIn || "",
      checkOut: item.checkOut || "",
      isReturn: Boolean(item.isReturn),
      stayKey: item.stayKey,
      sourceStayKey: item.sourceStayKey || null,
      city_id: item.city_id || null,
      country: item.country || null,
    }))
    .filter((item) => item.city);

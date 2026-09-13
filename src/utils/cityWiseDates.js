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

export const findCityDatesEntry = (cityWiseDates, cityName) => {
  if (!cityName || !Array.isArray(cityWiseDates) || !cityWiseDates.length) {
    return null;
  }
  const needle = toCityOnly(cityName).toLowerCase();
  return (
    cityWiseDates.find(
      (entry) => toCityOnly(entry.city).toLowerCase() === needle
    ) || null
  );
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
  tourCheckOut
) => {
  const entry = findCityDatesEntry(cityWiseDates, cityName);
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
}) => {
  if (!Array.isArray(cityWiseDates) || !cityWiseDates.length) return [];

  const active = toCityOnly(activeCityName).toLowerCase();
  return cityWiseDates.map((entry) => ({
    ...entry,
    booked: isCityServiceBooked(entry, bookings),
    active: toCityOnly(entry.city).toLowerCase() === active,
  }));
};

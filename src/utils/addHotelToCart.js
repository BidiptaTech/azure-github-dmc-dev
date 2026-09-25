import { addToCart } from "@/slice/cart/carSlice";
import { store } from "@/store/store";
import { lockCartDmc } from "@/utils/lockCartDmc";
import {
  getCountryForCityFromDestination,
  normalizeDestinationLocations,
  toCityOnly,
} from "@/utils/locationFormat";

const cloneRooms = (bookingArray) => {
  try {
    return JSON.parse(JSON.stringify(bookingArray || []));
  } catch {
    return Array.isArray(bookingArray) ? [...bookingArray] : [];
  }
};

const countNights = (checkIn, checkOut) => {
  if (!checkIn || !checkOut) return 0;
  const start = new Date(checkIn);
  const end = new Date(checkOut);
  const diff = end - start;
  if (Number.isNaN(diff) || diff <= 0) return 0;
  return Math.round(diff / (1000 * 3600 * 24));
};

/**
 * Resolve city/country for a hotel cart item.
 * Prefer hotel fields, then search/tour destination ({ city, country }[]).
 */
const resolveHotelCityCountry = ({ hotelDetails, tourDetails, searchState }) => {
  let city =
    toCityOnly(
      hotelDetails?.city ||
        hotelDetails?.City ||
        hotelDetails?.location ||
        hotelDetails?.address ||
        ""
    ) || "";

  let country = String(
    hotelDetails?.country || hotelDetails?.Country || ""
  ).trim();

  // "City, Country" style location/address on hotel
  if (!country) {
    const raw = String(
      hotelDetails?.location || hotelDetails?.address || ""
    ).trim();
    const parts = raw.split(",").map((p) => p.trim()).filter(Boolean);
    if (parts.length > 1) {
      if (!city) city = parts[0];
      country = parts.slice(1).join(", ");
    }
  }

  const destinationSources = [
    tourDetails?.destination,
    searchState?.location,
    tourDetails?.cityWiseDates,
    searchState?.cityWiseDates,
  ];

  for (const source of destinationSources) {
    const normalized = normalizeDestinationLocations(
      Array.isArray(source) ? source : source ? [source] : []
    );
    if (!normalized.length) continue;

    if (!city) {
      city = normalized[0].city || "";
    }
    if (!country) {
      country =
        getCountryForCityFromDestination(normalized, city) ||
        normalized.find((d) => d.country)?.country ||
        "";
    }
    if (city && country) break;
  }

  if (!country) {
    country = String(tourDetails?.country || "").trim();
  }

  return { city, country };
};

export const buildHotelCartItem = ({
  bookingArray,
  totalPrice,
  priceMode,
  hotelDetails,
  tourDetails,
  searchState,
  dmcId,
}) => {
  const rooms = cloneRooms(bookingArray);
  const check_in =
    tourDetails?.check_in ||
    tourDetails?.CheckInTime ||
    searchState?.ucheckIn ||
    "";
  const check_out =
    tourDetails?.check_out ||
    tourDetails?.CheckOutTime ||
    searchState?.ucheckOut ||
    "";

  const roomCount = rooms.reduce((sum, room) => {
    const beds = Array.isArray(room.beds) ? room.beds : [];
    const fromBeds = beds.reduce(
      (bedSum, bed) => bedSum + (Number(bed.head_count) || 0),
      0
    );
    return sum + (fromBeds || 1);
  }, 0);

  const { city, country } = resolveHotelCityCountry({
    hotelDetails,
    tourDetails,
    searchState,
  });
  const addressFromCityCountry = [city, country].filter(Boolean).join(", ");

  return {
    hotel_id: hotelDetails?.hotel_id || hotelDetails?.id || "",
    hotel_name: hotelDetails?.hotel_name || "",
    image: hotelDetails?.image || "",
    city,
    country,
    address:
      hotelDetails?.address ||
      hotelDetails?.location ||
      addressFromCityCountry ||
      "",
    location: hotelDetails?.location || city || "",
    bookingArray: rooms,
    totalPrice: Math.ceil(Number(totalPrice) || 0),
    priceMode: priceMode || null,
    pricemode: priceMode || null,
    check_in,
    check_out,
    bookingDate: check_in,
    adults: Number(tourDetails?.adult ?? searchState?.guests?.adults ?? 0),
    children: Number(tourDetails?.child ?? searchState?.guests?.children ?? 0),
    infant: Number(tourDetails?.infant ?? searchState?.guests?.infant ?? 0),
    nights: countNights(check_in, check_out),
    roomCount,
    room_summary: rooms
      .map((room) => room.room_type)
      .filter(Boolean)
      .join(", "),
    dmc_id:
      dmcId ??
      hotelDetails?.dmc_id ??
      hotelDetails?.dmcId ??
      null,
  };
};

export const addHotelBookingToCart = (dispatch, payload) => {
  if (!Array.isArray(payload?.bookingArray) || payload.bookingArray.length === 0) {
    return "Please select a room first.";
  }

  const currentDmcId = store.getState().dmc?.dmcId;
  const item = buildHotelCartItem({
    ...payload,
    dmcId: payload.dmcId ?? currentDmcId,
  });

  dispatch(
    addToCart({
      bookingType: "hotel",
      item,
      tourDetails: {
        ...(payload.tourDetails || {}),
        dmc_id: item.dmc_id,
      },
    })
  );

  const cartError = store.getState().cart?.lastActionError;
  if (!cartError) {
    lockCartDmc(dispatch, item);
  }
  return cartError || null;
};

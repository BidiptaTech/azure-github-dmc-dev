 import { addToCart } from "@/slice/cart/carSlice";
import { store } from "@/store/store";

const clonePayload = (value) => {
  try {
    return JSON.parse(JSON.stringify(value ?? null));
  } catch {
    return value ?? null;
  }
};

export const buildAttractionCartItem = ({
  bookingDetails,
  attraction,
  attractionDetails,
}) => {
  const row = bookingDetails?.data?.[0] || {};
  const image =
    attraction?.image ||
    attractionDetails?.master_image ||
    attraction?.master_image ||
    "";
  const city = attraction?.city || attractionDetails?.city || "";
  const country = attraction?.country || attractionDetails?.country || "";

  return {
    AttractionId: row.AttractionId || attraction?.id || attractionDetails?.id || "",
    AttractionName:
      row.AttractionName ||
      attraction?.attraction_name ||
      attractionDetails?.name ||
      "Attraction",
    image,
    city,
    country,
    address: [city, country].filter(Boolean).join(", "),
    location: city,
    bookingDate: row.bookingDate || "",
    visitTime: row.visitTime || "",
    adults: Number(row.adultCount || 0),
    children: Number(row.childCount || 0),
    seniorCount: Number(row.seniorCount || 0),
    ticketId: row.ticketId || null,
    ticketName: row.ticketName || "",
    Selection: row.Selection || "",
    transport: clonePayload(row.transport),
    mode: row.mode || null,
    pricemode: row.mode || null,
    totalPrice: Math.ceil(Number(row.totalPrice) || 0),
    bookingPayload: clonePayload(bookingDetails),
  };
};

export const addAttractionBookingToCart = (dispatch, payload) => {
  const row = payload?.bookingDetails?.data?.[0];
  if (!row) {
    return "Please complete attraction selections first.";
  }

  dispatch(
    addToCart({
      bookingType: "attraction",
      item: buildAttractionCartItem(payload),
      tourDetails: payload.tourDetails,
    })
  );

  return store.getState().cart?.lastActionError || null;
};

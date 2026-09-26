import { addToCart } from "@/slice/cart/carSlice";
import { store } from "@/store/store";
import { lockCartDmc } from "@/utils/lockCartDmc";

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
    dmc_id:
      row.dmc_id ||
      attractionDetails?.prices?.dmc_id ||
      attraction?.dmc_id ||
      null,
  };
};

export const addAttractionBookingToCart = (dispatch, payload) => {
  const row = payload?.bookingDetails?.data?.[0];
  if (!row) {
    return "Please complete attraction selections first.";
  }

  const item = buildAttractionCartItem(payload);
  if (item.dmc_id == null) {
    item.dmc_id = store.getState().dmc?.dmcId || null;
  }

  dispatch(
    addToCart({
      bookingType: "attraction",
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

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

const mealSummary = (row) => {
  const parts = [row?.mealType, row?.mealSpecificType].filter(Boolean);
  const items = Array.isArray(row?.MealDescription)
    ? row.MealDescription.map((meal) => meal.item_name || meal.name).filter(Boolean)
    : [];
  if (items.length) {
    return [...parts, items.join(", ")].filter(Boolean).join(" · ");
  }
  return parts.join(" · ");
};

export const buildRestaurantCartItem = ({
  bookingDetails,
  restaurant,
  restaurantsDetails,
}) => {
  const row = bookingDetails?.data?.[0] || {};
  const image =
    restaurant?.image ||
    restaurantsDetails?.master_image ||
    restaurant?.master_image ||
    restaurantsDetails?.image ||
    "";
  const city = restaurant?.city || restaurantsDetails?.city || "";
  const country = restaurant?.country || restaurantsDetails?.country || "";

  return {
    restaurantId: row.restaurantId || restaurant?.id || restaurantsDetails?.id || "",
    restaurantName:
      row.restaurantName ||
      restaurant?.restaurant_name ||
      restaurantsDetails?.name ||
      "Restaurant",
    image,
    city,
    country,
    address: [city, country].filter(Boolean).join(", "),
    location: city,
    bookingDate: row.bookingDate || "",
    visitTime: row.visitTime || "",
    adults: Number(row.adultCount || 0),
    children: Number(row.childCount || 0),
    mealType: row.mealType || "",
    mealSpecificType: row.mealSpecificType || "",
    meal_summary: mealSummary(row),
    pricemode: row.mealType || null,
    totalPrice: Math.ceil(Number(row.totalPrice) || 0),
    bookingPayload: clonePayload(bookingDetails),
    dmc_id:
      row.dmc_id ||
      restaurant?.dmc_id ||
      restaurantsDetails?.dmc_id ||
      restaurantsDetails?.prices?.dmc_id ||
      null,
  };
};

export const addRestaurantBookingToCart = (dispatch, payload) => {
  const row = payload?.bookingDetails?.data?.[0];
  if (!row) {
    return "Please complete restaurant selections first.";
  }

  const item = buildRestaurantCartItem(payload);
  if (item.dmc_id == null) {
    item.dmc_id = store.getState().dmc?.dmcId || null;
  }

  dispatch(
    addToCart({
      bookingType: "restaurant",
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

import { addToCart } from "@/slice/cart/carSlice";
import { store } from "@/store/store";
import { lockCartDmc } from "@/utils/lockCartDmc";

export const buildGuideCartItem = (details = {}) => ({
  guide_id: details.guide_id || "",
  guide_name: details.guide_name || "Guide",
  image: details.image || "",
  dmc_Id: details.dmc_Id || null,
  dmc_id: details.dmc_id ?? details.dmc_Id ?? null,
  Mode: details.Mode || null,
  pricemode: details.Mode || null,
  entrypickup: details.entrypickup || "",
  PickupPlaceid: details.PickupPlaceid || null,
  DropoffPlaceid: details.DropoffPlaceid || null,
  bookingDate: details.bookingDate || "",
  pickupdate: details.pickupdate || "",
  entrytime: details.entrytime || "",
  adults: Number(details.adults || 0),
  children: Number(details.children || 0),
  hours: details.hours || "",
  basePrice: details.basePrice || 0,
  surcharge: details.surcharge || 0,
  totalPrice: Math.ceil(Number(details.totalPrice) || 0),
  Tax: details.Tax || null,
  Night_Start_Time: details.Night_Start_Time || null,
  Night_End_Time: details.Night_End_Time || null,
  city: details.city || details.entrypickup || "",
  country: details.country || "",
});

export const addGuideBookingToCart = (dispatch, { details, tourDetails }) => {
  if (!details?.guide_id) {
    return "Please select a guide and complete the booking details.";
  }

  const item = buildGuideCartItem(details);
  if (item.dmc_id == null) {
    item.dmc_id = store.getState().dmc?.dmcId || null;
  }

  dispatch(
    addToCart({
      bookingType: "guide",
      item,
      tourDetails: {
        ...(tourDetails || {}),
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

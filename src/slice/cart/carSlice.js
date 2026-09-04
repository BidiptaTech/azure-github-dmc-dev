import { createSlice, current } from "@reduxjs/toolkit";

/**
 * Cart grouped by tour trip details:
 * [
 *   {
 *     tripId, check_in, check_out, destination, adult, child, infant, tour_id,
 *     cityWiseDates: [{ city, checkIn, checkOut }, ...],
 *     bookings: [
 *       { type: "entryport", cartItemId, ... },
 *       { type: "exitport", cartItemId, ... },
 *     ]
 *   },
 * ]
 */
export const MAX_CART_TRIPS = 5;
const CART_STORAGE_KEY = "dmc_cart";

const loadCartFromStorage = () => {
  try {
    if (typeof window === "undefined") return [];
    const raw = localStorage.getItem(CART_STORAGE_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch (error) {
    console.error("Failed to load cart from localStorage:", error);
    return [];
  }
};

const saveCartToStorage = (cart) => {
  try {
    if (typeof window === "undefined") return;
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart ?? []));
  } catch (error) {
    console.error("Failed to save cart to localStorage:", error);
  }
};

const persistCart = (state) => {
  const plainCart = current(state).cart;
  console.log("state.cart added to cart", plainCart);
  console.log("state.cart JSON:", JSON.stringify(plainCart, null, 2));
  saveCartToStorage(plainCart);
};

const initialState = {
  cart: loadCartFromStorage(),
  lastActionError: null,
  checkoutTripId: null,
};

const normalizeDestination = (destination) => {
  if (Array.isArray(destination)) {
    return destination.map(String).join("|");
  }
  if (destination == null) return "";
  return String(destination);
};

export const buildTourMeta = (tourDetails = {}) => {
  const check_in =
    tourDetails.check_in ||
    tourDetails.CheckInTime ||
    tourDetails.checkIn ||
    "";
  const check_out =
    tourDetails.check_out ||
    tourDetails.CheckOutTime ||
    tourDetails.checkOut ||
    "";
  // Keep same shape as handleSearch: [{ city, country }, ...]
  const destination = tourDetails.destination ?? "";
  const adult = Number(tourDetails.adult ?? tourDetails.Adults ?? 0);
  const child = Number(tourDetails.child ?? tourDetails.Children ?? 0);
  const infant = Number(tourDetails.infant ?? tourDetails.Infants ?? 0);
  const cityWiseDates = Array.isArray(tourDetails.cityWiseDates)
    ? tourDetails.cityWiseDates
    : [];
  const country =
    tourDetails.country ||
    (Array.isArray(destination) &&
      destination.find((d) => d?.country)?.country) ||
    "";

  return {
    check_in,
    check_out,
    destination,
    country,
    adult,
    child,
    infant,
    cityWiseDates,
    adultGenders: Array.isArray(tourDetails.adultGenders)
      ? tourDetails.adultGenders
      : undefined,
    childrenAges: Array.isArray(tourDetails.childrenAges)
      ? tourDetails.childrenAges
      : undefined,
    tour_id: tourDetails.tour_id ?? tourDetails.tourId ?? null,
  };
};

export const isSameTrip = (trip, meta) =>
  String(trip.check_in || "") === String(meta.check_in || "") &&
  String(trip.check_out || "") === String(meta.check_out || "") &&
  normalizeDestination(trip.destination) ===
    normalizeDestination(meta.destination);

export const wouldCreateNewCartTrip = (cart, tourDetails) => {
  const meta = buildTourMeta(tourDetails || {});
  const list = Array.isArray(cart) ? cart : [];
  return !list.some((trip) => isSameTrip(trip, meta));
};

const cartSlice = createSlice({
  name: "cart",
  initialState,
  reducers: {
    addToCart: (state, action) => {
      const { bookingType, item, tourDetails } = action.payload || {};
      state.lastActionError = null;

      if (!bookingType || !item) {
        state.lastActionError = "Invalid cart item.";
        return;
      }

      const meta = buildTourMeta(tourDetails || {});
      const booking = {
        ...item,
        pricemode: item.pricemode || item.type || null,
        type: bookingType,
        cartItemId: `${bookingType}-${Date.now()}-${Math.random()
          .toString(36)
          .slice(2, 8)}`,
        addedAt: new Date().toISOString(),
      };

      if (!Array.isArray(state.cart)) {
        state.cart = [];
      }

      const existingIndex = state.cart.findIndex((trip) =>
        isSameTrip(trip, meta)
      );

      if (existingIndex >= 0) {
        if (!Array.isArray(state.cart[existingIndex].bookings)) {
          state.cart[existingIndex].bookings = [];
        }
        state.cart[existingIndex].adult = meta.adult;
        state.cart[existingIndex].child = meta.child;
        state.cart[existingIndex].infant = meta.infant;
        state.cart[existingIndex].destination = meta.destination;
        if (meta.country) {
          state.cart[existingIndex].country = meta.country;
        }
        if (meta.cityWiseDates?.length) {
          state.cart[existingIndex].cityWiseDates = meta.cityWiseDates;
        }
        if (meta.adultGenders) {
          state.cart[existingIndex].adultGenders = meta.adultGenders;
        }
        if (meta.childrenAges) {
          state.cart[existingIndex].childrenAges = meta.childrenAges;
        }
        if (meta.tour_id != null) {
          state.cart[existingIndex].tour_id = meta.tour_id;
        }
        state.cart[existingIndex].bookings.push(booking);
      } else {
        if (state.cart.length >= MAX_CART_TRIPS) {
          state.lastActionError = `Maximum ${MAX_CART_TRIPS} trips can be added to the cart.`;
          return;
        }
        state.cart.push({
          tripId: `trip-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
          ...meta,
          bookings: [booking],
        });
      }

      persistCart(state);
    },
    removeFromCart: (state, action) => {
      const { tripId, cartItemId } = action.payload || {};
      state.lastActionError = null;
      if (!tripId || !cartItemId || !Array.isArray(state.cart)) return;

      const tripIndex = state.cart.findIndex((trip) => trip.tripId === tripId);
      if (tripIndex < 0) return;

      const trip = state.cart[tripIndex];
      trip.bookings = (trip.bookings || []).filter(
        (booking) => booking.cartItemId !== cartItemId
      );

      if (!trip.bookings.length) {
        state.cart.splice(tripIndex, 1);
      }

      persistCart(state);
    },
    clearCartByTrip: (state, action) => {
      const tripId = action.payload;
      state.lastActionError = null;
      if (!tripId || !Array.isArray(state.cart)) return;
      state.cart = state.cart.filter((trip) => trip.tripId !== tripId);
      if (state.checkoutTripId === tripId) {
        state.checkoutTripId = null;
      }
      persistCart(state);
    },
    clearCart: (state) => {
      state.cart = [];
      state.checkoutTripId = null;
      state.lastActionError = null;
      persistCart(state);
    },
    setCheckoutTripId: (state, action) => {
      state.checkoutTripId = action.payload || null;
    },
    clearCartError: (state) => {
      state.lastActionError = null;
    },
  },
});

export const {
  addToCart,
  removeFromCart,
  clearCartByTrip,
  clearCart,
  setCheckoutTripId,
  clearCartError,
} = cartSlice.actions;

export const selectCart = (state) =>
  Array.isArray(state.cart?.cart) ? state.cart.cart : [];

export const selectCartLastError = (state) => state.cart?.lastActionError;

export const selectCheckoutTripId = (state) => state.cart?.checkoutTripId;

export const selectCartItemCount = (state) => {
  const cart = Array.isArray(state.cart?.cart) ? state.cart.cart : [];
  return cart.reduce(
    (sum, trip) =>
      sum + (Array.isArray(trip.bookings) ? trip.bookings.length : 0),
    0
  );
};

export default cartSlice.reducer;

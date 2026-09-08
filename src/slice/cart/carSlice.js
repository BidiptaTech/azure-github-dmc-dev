import { createSlice, current } from "@reduxjs/toolkit";

/**
 * Cart grouped by tour trip details:
 * [
 *   {
 *     tripId, check_in, check_out, destination, adult, child, infant, tour_id,
 *     cityWiseDates: [{ city, checkIn, checkOut }, ...],
 *     searchLocation: ["SG", "ID"],
 *     customerInfo: {
 *       fullName, email, phone, countryCode, address1, address2, state, zip, specialRequests
 *     } | null,
 *     dmc_id: 123, // DMC locked for this cart trip
 *     bookings: [
 *       { type: "hotel"|"attraction"|"restaurant"|"guide"|"entryport"|..., cartItemId, ... },
 *     ]
 *   },
 * ]
 *
 * Final submit (Book Now / Make an Enquiry) reads trip.bookings + trip.customerInfo.
 */
export const MAX_CART_TRIPS = 1;
const CART_STORAGE_KEY = "dmc_cart";

export const emptyCartCustomerInfo = () => ({
  fullName: "",
  email: "",
  phone: "",
  countryCode: "",
  address1: "",
  address2: "",
  state: "",
  zip: "",
  specialRequests: "",
});

export const normalizeCartCustomerInfo = (raw) => {
  if (!raw || typeof raw !== "object") return null;
  const source = raw.userInfo || raw.customer_info || raw.customerInfo || raw;
  const normalized = {
    fullName: source.fullName || source.full_name || source.name || "",
    email: source.email || "",
    phone: source.phone || source.phone_number || "",
    countryCode: source.countryCode || source.country_code || "",
    address1: source.address1 || source.address_1 || source.address || "",
    address2: source.address2 || source.address_2 || "",
    state: source.state || "",
    zip: source.zip || source.postal_code || "",
    specialRequests:
      source.specialRequests || source.special_requests || source.comment || "",
  };
  const hasAny = Object.values(normalized).some((v) => String(v || "").trim());
  return hasAny ? normalized : null;
};

const loadCartFromStorage = () => {
  try {
    if (typeof window === "undefined") return [];
    const raw = localStorage.getItem(CART_STORAGE_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    const list = Array.isArray(parsed) ? parsed : [];
    // Enforce single-trip cart (trim older multi-trip data)
    return list.slice(0, MAX_CART_TRIPS).map((trip) => ({
      ...trip,
      customerInfo: normalizeCartCustomerInfo(trip?.customerInfo) || null,
      bookings: Array.isArray(trip?.bookings) ? trip.bookings : [],
    }));
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
  const searchLocation = Array.isArray(tourDetails.searchLocation)
    ? tourDetails.searchLocation.filter(Boolean)
    : Array.isArray(tourDetails.countryCodes)
      ? tourDetails.countryCodes.filter(Boolean)
      : [];
  const dmc_id =
    tourDetails.dmc_id ??
    tourDetails.dmc_Id ??
    tourDetails.dmcId ??
    null;

  return {
    check_in,
    check_out,
    destination,
    country,
    searchLocation,
    dmc_id: dmc_id != null && dmc_id !== "" ? Number(dmc_id) : null,
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
      const itemDmcId =
        item.dmc_id ?? item.dmc_Id ?? item.dmcId ?? meta.dmc_id ?? null;
      const booking = {
        ...item,
        dmc_id:
          itemDmcId != null && itemDmcId !== "" ? Number(itemDmcId) : null,
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
        if (meta.searchLocation?.length) {
          state.cart[existingIndex].searchLocation = meta.searchLocation;
        }
        if (booking.dmc_id != null) {
          state.cart[existingIndex].dmc_id = booking.dmc_id;
        } else if (meta.dmc_id != null) {
          state.cart[existingIndex].dmc_id = meta.dmc_id;
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
        if (state.cart[existingIndex].customerInfo === undefined) {
          state.cart[existingIndex].customerInfo = null;
        }
        state.cart[existingIndex].bookings.push(booking);
      } else {
        if (state.cart.length >= MAX_CART_TRIPS) {
          state.lastActionError =
            "Only one trip is allowed in the cart. Start a new search to replace it.";
          return;
        }
        state.cart.push({
          tripId: `trip-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
          ...meta,
          customerInfo: null,
          bookings: [booking],
        });
      }

      persistCart(state);
    },
    /**
     * Attach / update customer details on a trip (persisted with dmc_cart).
     * Used before final Book Now / Make an Enquiry submit.
     */
    setTripCustomerInfo: (state, action) => {
      const { tripId, customerInfo } = action.payload || {};
      state.lastActionError = null;
      if (!tripId || !Array.isArray(state.cart)) {
        state.lastActionError = "Trip not found for customer info.";
        return;
      }
      const trip = state.cart.find((t) => t.tripId === tripId);
      if (!trip) {
        state.lastActionError = "Trip not found for customer info.";
        return;
      }
      trip.customerInfo = normalizeCartCustomerInfo(customerInfo);
      persistCart(state);
    },
    clearTripCustomerInfo: (state, action) => {
      const tripId = action.payload;
      state.lastActionError = null;
      if (!tripId || !Array.isArray(state.cart)) return;
      const trip = state.cart.find((t) => t.tripId === tripId);
      if (!trip) return;
      trip.customerInfo = null;
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
  setTripCustomerInfo,
  clearTripCustomerInfo,
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

export const selectCartTripById = (tripId) => (state) => {
  const cart = Array.isArray(state.cart?.cart) ? state.cart.cart : [];
  return cart.find((trip) => trip.tripId === tripId) || null;
};

export const selectTripCustomerInfo = (tripId) => (state) => {
  const trip = (Array.isArray(state.cart?.cart) ? state.cart.cart : []).find(
    (t) => t.tripId === tripId
  );
  return trip?.customerInfo || null;
};

/** Active checkout trip (checkoutTripId or first/only trip) */
export const selectCheckoutTrip = (state) => {
  const cart = Array.isArray(state.cart?.cart) ? state.cart.cart : [];
  const id = state.cart?.checkoutTripId;
  if (id) {
    return cart.find((trip) => trip.tripId === id) || cart[0] || null;
  }
  return cart[0] || null;
};

export default cartSlice.reducer;

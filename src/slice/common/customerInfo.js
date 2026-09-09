import { createSlice } from "@reduxjs/toolkit";

function pickCustomerFields(raw) {
  if (!raw || typeof raw !== "object") return null;

  // Booking API payloads sometimes nest customer fields
  const source = raw.userInfo || raw.customer_info || raw.customerInfo || raw;

  const email = source.email || "";
  const phone = source.phone || source.phone_number || "";
  const countryCode = source.countryCode || source.country_code || "";

  // Prefer explicit customer name fields. Generic `name` is only used when email/phone
  // are present — service booking records often have a `name` (hotel/attraction/etc.).
  const explicitName = source.fullName || source.full_name || "";
  const fullName =
    explicitName ||
    ((email || phone) && source.name ? source.name : "") ||
    "";

  // Service booking records lack customer contact fields — ignore them
  if (!fullName && !email && !phone) {
    return null;
  }
  // Reject payloads that only have a generic name and no contact info
  if (!explicitName && !email && !phone) {
    return null;
  }

  return {
    fullName: fullName || "",
    email: email || "",
    phone: phone || "",
    countryCode: countryCode || "",
    address1: source.address1 || source.address_1 || source.address || "",
    address2: source.address2 || source.address_2 || "",
    state: source.state || "",
    zip: source.zip || source.postal_code || "",
    specialRequests:
      source.specialRequests || source.special_requests || source.comment || "",
  };
}

const getInitialState = () => {
  const savedUserInfo = localStorage.getItem("userInfo");
  const savedBookingResponse = localStorage.getItem("bookingResponse");
  const parsedUserInfo = savedUserInfo ? JSON.parse(savedUserInfo) : null;

  return {
    userInfo: parsedUserInfo || {
      fullName: "",
      email: "",
      phone: "",
      countryCode: "",
      address1: "",
      address2: "",
      state: "",
      zip: "",
      specialRequests: "",
    },
    bookingResponse: savedBookingResponse
      ? JSON.parse(savedBookingResponse)
      : null,
    isCustomerInfoFilled: Boolean(parsedUserInfo?.fullName),
  };
};

const initialState = getInitialState();

const customerInfoSlice = createSlice({
  name: "customerInfo",
  initialState,
  reducers: {
    setUserInfo: (state, action) => {
      // Handle both array and direct object formats
      const userData = Array.isArray(action.payload)
        ? action.payload[0]
        : action.payload;

      const next = pickCustomerFields(userData);

      // Ignore service booking payloads that would wipe existing customer info
      if (!next) {
        return;
      }

      state.userInfo = next;
      state.isCustomerInfoFilled = Boolean(next.fullName);
      localStorage.setItem("userInfo", JSON.stringify(state.userInfo));
    },
    setBookingResponse: (state, action) => {
      state.bookingResponse = action.payload;
      localStorage.setItem("bookingResponse", JSON.stringify(action.payload));

      const responseData =
        action.payload?.data?.[0] ||
        action.payload?.service?.data?.[0] ||
        null;
      const next = pickCustomerFields(responseData);
      if (next?.fullName) {
        state.userInfo = {
          ...state.userInfo,
          ...next,
          fullName: next.fullName || state.userInfo.fullName,
          email: next.email || state.userInfo.email,
          phone: next.phone || state.userInfo.phone,
          countryCode: next.countryCode || state.userInfo.countryCode,
          address1: next.address1 || state.userInfo.address1,
          address2: next.address2 || state.userInfo.address2,
          state: next.state || state.userInfo.state,
          zip: next.zip || state.userInfo.zip,
          specialRequests:
            next.specialRequests || state.userInfo.specialRequests,
        };
        state.isCustomerInfoFilled = true;
        localStorage.setItem("userInfo", JSON.stringify(state.userInfo));
      }
    },
    clearUserInfo: (state) => {
      state.userInfo = {
        fullName: "",
        email: "",
        phone: "",
        countryCode: "",
        address1: "",
        address2: "",
        state: "",
        zip: "",
        specialRequests: "",
      };
      state.bookingResponse = null;
      state.isCustomerInfoFilled = false;

      localStorage.removeItem("userInfo");
      localStorage.removeItem("bookingResponse");
      localStorage.removeItem("isSubmitted");
    },
    resetBookingState: (state) => {
      state.bookingResponse = null;
      localStorage.removeItem("bookingResponse");
    },
  },
});

export const {
  setUserInfo,
  setBookingResponse,
  clearUserInfo,
  resetBookingState,
} = customerInfoSlice.actions;

export const selectUserInfo = (state) => state.customerInfo?.userInfo;
export const selectBookingResponse = (state) =>
  state.customerInfo?.bookingResponse;
export const selectIsCustomerInfoFilled = (state) =>
  state.customerInfo?.isCustomerInfoFilled;

export default customerInfoSlice.reducer;

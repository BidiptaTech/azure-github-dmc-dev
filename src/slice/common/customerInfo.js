import { createSlice } from "@reduxjs/toolkit";

const getInitialState = () => {
  const savedUserInfo = localStorage.getItem("userInfo");
  const savedBookingResponse = localStorage.getItem("bookingResponse");

  return {
    userInfo: savedUserInfo
      ? JSON.parse(savedUserInfo)
      : {
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
    isCustomerInfoFilled: Boolean(savedUserInfo), // true if we have saved user info
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

      state.userInfo = {
        fullName: userData.fullName || "",
        email: userData.email || "",
        phone: userData.phone || "",
        countryCode: userData.countryCode || "",
        address1: userData.address1 || "",
        address2: userData.address2 || "",
        state: userData.state || "",
        zip: userData.zip || "",
        specialRequests: userData.specialRequests || "",
      };
      // console.log("state.userInfo", state.userInfo);
      state.isCustomerInfoFilled = true;
      //localStorage.setItem('userInfo', JSON.stringify(state.userInfo));
    },
    setBookingResponse: (state, action) => {
      state.bookingResponse = action.payload;
      localStorage.setItem("bookingResponse", JSON.stringify(action.payload));

      if (action.payload?.data?.[0]) {
        const responseData = action.payload.data[0];
        state.userInfo = {
          fullName: responseData.fullName || state.userInfo.fullName,
          email: responseData.email || state.userInfo.email,
          phone: responseData.phone || state.userInfo.phone,
          countryCode: responseData.countryCode || state.userInfo.countryCode,
          address1: responseData.address1 || state.userInfo.address1,
          address2: responseData.address2 || state.userInfo.address2,
          state: responseData.state || state.userInfo.state,
          zip: responseData.zip || state.userInfo.zip,
          specialRequests:
            responseData.specialRequests || state.userInfo.specialRequests,
        };
        localStorage.setItem("userInfo", JSON.stringify(state.userInfo));
      }
    },
    clearUserInfo: (state) => {
      // Reset all state properties
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

      // Clear all localStorage items
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

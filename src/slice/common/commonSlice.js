import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  bookingType: null, // can be 'booking' or 'enquiry'
  bookingMode: "dmc", // Default to 'dmc', will be updated based on selection
  isNavigating: false,
  selectedCity: null, 
};

const commonSlice = createSlice({
  name: "common",
  initialState,
  reducers: {
    setBookingType: (state, action) => {
      state.bookingType = action.payload;
      // console.log("Setting bookingType to:", action.payload);
    },
    setBookingMode: (state, action) => {
      // console.log("Setting bookingMode to:", action.payload);
      state.bookingMode = action.payload;
    },
    setIsNavigating: (state, action) => {
      state.isNavigating = action.payload;
    },
    setSelectedCity: (state, action) => {
      // Log the type of payload to help debug
      console.log("setSelectedCity called with payload:", action.payload);
      console.log("Payload type:", typeof action.payload);
      
      // Ensure we handle null correctly
      if (action.payload === null) {
        state.selectedCity = null;
        console.log("City in commonSlice set to null");
        return;
      }
      
      // Store the city data
      state.selectedCity = action.payload;
      console.log("City stored in commonSlice:", state.selectedCity);
    },
  },
});

export const { setBookingType, setBookingMode, setIsNavigating, setSelectedCity } =
  commonSlice.actions;
export const selectBookingType = (state) => state.common.bookingType;
export const selectIsNavigating = (state) => state.common.isNavigating;
export const selectBookingMode = (state) => state.common.bookingMode;
export default commonSlice.reducer;

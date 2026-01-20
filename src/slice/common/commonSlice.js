import { createAsyncThunk, createSlice } from "@reduxjs/toolkit";
import Cookies from "js-cookie";
import { BASE_URL } from "@/services/api";
import axios from "axios";

export const singleBooking = createAsyncThunk(
  "singleBooking/common",
  async ({bookingId, tourId, cancelReason}, { rejectWithValue, getState }) => {
    try {
      const userRole = getState().auth?.userRole;
      const authToken = Cookies.get("authToken");
      const AgentId = userRole === "Sales Head(DMC)" || userRole === "Sales Manager (DMC)" || userRole === "Assistant Manager (DMC)" ? getState().editing.agentId : Cookies.get("AgentId");
      // console.log("bookingIdcommon", bookingId);
      // console.log("tourIdcommon", tourId);
      // console.log("AgentIdcommon", AgentId);
      // console.log("authTokencommon", authToken);
      
      const response = await axios.post(`${BASE_URL}/cancel-booking`, {
        booking_id: String(bookingId),
        agent_id: String(AgentId),
        tour_id: String(tourId),
        cancel_reason: String(cancelReason),
      }, {
        headers: {
          "Authorization": `Bearer ${authToken}`,
          "Content-Type": "application/json",
        }
      });
      
      // console.log("API response:", response.data);
      return response.data;
    } catch (error) {
      console.error("API error:", error.response?.data || error.message);
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);


const initialState = {
  bookingType: null, // can be 'booking' or 'enquiry'
  bookingMode: "dmc", // Default to 'dmc', will be updated based on selection
  isNavigating: false,
  haveBooking: false,
  selectedCity: null,
  guestCounts: {
    Adults: 1,
    Children: 0,
    Infants: 0,
    maleCount: 0,
    femaleCount: 0,
    ages: []
  },
  shouldResetGuestSearch: false
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
    setGuestCounts: (state, action) => {
      state.guestCounts = action.payload;
    },
    setHaveBooking: (state, action) => {
      state.haveBooking = action.payload;
    },
    resetGuestSearch: (state) => {
      state.guestCounts = {
        Adults: 1,
        Children: 0,
        Infants: 0,
        maleCount: 0,
        femaleCount: 0,
        ages: []
      };
      state.shouldResetGuestSearch = true;
    },
    clearGuestSearchReset: (state) => {
      state.shouldResetGuestSearch = false;
    },
  },
});

export const { setBookingType, setBookingMode, setIsNavigating, setSelectedCity, setGuestCounts, resetGuestSearch, clearGuestSearchReset, setHaveBooking } =
  commonSlice.actions;
export const selectBookingType = (state) => state.common.bookingType;
export const selectIsNavigating = (state) => state.common.isNavigating;
export const selectBookingMode = (state) => state.common.bookingMode;
export const selectGuestCounts = (state) => state.common.guestCounts;
export const selectShouldResetGuestSearch = (state) => state.common.shouldResetGuestSearch;
export const selectHaveBooking = (state) => state.common.haveBooking;
export default commonSlice.reducer;

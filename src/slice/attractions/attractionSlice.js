import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { setUserInfo, setBookingResponse } from "../common/customerInfo";
import { setDateService } from "../common/dateServicesSlice";
import { BASE_URL } from "@/services/api";
import { selectDmcId } from "../dmc/dmcSlice";

// Async thunk to fetch attractions
export const fetchAttractions = createAsyncThunk(
  "attractions/fetchAttractions",
  async (
    { city, date, adults, children, tour_id, selectedDate, fromMainSearch },
    { rejectWithValue, dispatch }
  ) => {
    try {
      // If the search is coming from MainFilterSearchBox, return empty array
      if (fromMainSearch) {
        return [];
      }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      const queryParams = new URLSearchParams();

      // Handle date parameter
      if (date) {
        // Format date as {"0":"YYYY-MM-DD"}
        queryParams.append("date", JSON.stringify({ 0: date }));
      } else if (selectedDate) {
        // Fallback to selectedDate if date is not provided
        queryParams.append(
          "date",
          JSON.stringify({ 0: selectedDate.format("YYYY-MM-DD") })
        );
      } else {
        // If no date is provided, send an empty object
        queryParams.append("date", "{}");
      }

      // Append other query parameters as usual
      if (city) queryParams.append("city", city);
      if (adults) queryParams.append("adults", adults);
      if (children) queryParams.append("children", children);
      if (tour_id) queryParams.append("tour_id", tour_id);

      const response = await axios.get(
        `${BASE_URL}/attraction?${queryParams.toString()}`,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
          },
        }
      );
      // console.log('fetch attraction',response.data);

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        // console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
        dispatch(setAuthenticated(false));
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Async thunk to fetch attraction details
export const fetchAttractionDetails = createAsyncThunk(
  "attractions/fetchAttractionDetails",
  async (
    { attractionId, price_mode, dmc_id },
    { rejectWithValue, dispatch }
  ) => {
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // Construct the API URL with the mode and dmc_id parameters
      const mode = price_mode?.mode || "default_value";
      const apiUrl = `${BASE_URL}/attraction-details?attractionId=${attractionId}&mode=${mode}&dmc_id=${dmc_id}`;

      const response = await axios.get(apiUrl, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        await dispatch(logoutUser());
        dispatch(setAuthenticated(false));
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Async thunk to create a booking
export const createBooking = createAsyncThunk(
  "attractions/createBooking",
  async (bookingDetails, { rejectWithValue, getState, dispatch }) => {
    try {
      const state = getState();
      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;

      // Get selected DMC ID from Redux state
      const selectedDmcId = selectDmcId(state);
      console.log('🎯 AttractionsSlice - Selected DMC ID from Redux:', selectedDmcId);

      // Add selected DMC ID to booking details
      const updatedBookingDetails = {
        ...bookingDetails,
        data: bookingDetails.data.map(item => ({
          ...item,
          dmc_id: selectedDmcId // Use selected DMC ID from Redux store
        }))
      };

      console.log('🚀 AttractionsSlice - Booking with DMC ID:', selectedDmcId);

      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)"
      ) {
        AgentId = agentID;
      } else {
        AgentId = Cookies.get("AgentId");
      }

      const response = await axios.post(
        `${BASE_URL}/create-booking`,
        updatedBookingDetails,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
            "Content-Type": "application/json",
          },
        }
      );

      // Store the response in customerInfo slice
      dispatch(setBookingResponse(response.data));

      if (response?.data?.service?.date_service) {
        dispatch(setDateService(response.data.service.date_service));
        
        // Handle both attraction and attraction_package data
        const attractionData = response.data.service.attraction || [];
        const attractionPackageData = response.data.service.attraction_package || [];
        const combinedData = [...attractionData, ...attractionPackageData];
        
        dispatch(setAttractionService(combinedData));
      }

      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const initialState = {
  attractions: [],
  attractionDetails: null,
  filters: {
    category: "All",
    priceRange: { min: 0, max: 1000 },
    rating: 0,
    dateRange: [0, 365],
  },
  searchText: "",
  dateRange: [null, null],
  selectedAttraction: null,
  sortBy: "",
  checkoutData: null,
  status: "idle",
  error: null,
  bookingDate: null,
  bookings: [],
  services: [],
  filteredAttractions: [],
  searchParams: {},
  selectedModeData: {},
  attractionBookings: [],
};

const attractionsSlice = createSlice({
  name: "attractions",
  initialState,
  reducers: {
    addAttractionBookings: (state, action) => {
      state.attractionBookings = [action.payload];
    },
    setFilters: (state, action) => {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearFilters: (state) => {
      state.filters = { category: "All", priceRange: { min: "", max: "" } };
    },
    setSearchText: (state, action) => {
      state.searchText = action.payload;
    },
    setDateRange: (state, action) => {
      state.dateRange = action.payload;
    },
    setSelectedAttraction: (state, action) => {
      state.selectedAttraction = action.payload;
    },
    setSortBy: (state, action) => {
      state.sortBy = action.payload;
    },
    setCheckoutData: (state, action) => {
      state.checkoutData = action.payload;
    },
    setAttractionService: (state, action) => {
      console.log('AttractionService', action.payload);
      state.services = action.payload;
    },
    setSearchParams: (state, action) => {
      const serializedPayload = {
        ...action.payload,
        date: action.payload.date
          ? action.payload.date.format("YYYY-MM-DD")
          : null,
      };
      state.searchParams = serializedPayload;
    },
    setSelectedModeData: (state, action) => {
      const { attractionId, mode, adultPrice, childPrice } = action.payload;
      state.selectedModeData[attractionId] = {
        mode,
        adultPrice,
        childPrice,
      };
    },
    clearAttractions: (state) => {
      state.attractions = [];
      state.filteredAttractions = [];
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchAttractions.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchAttractions.fulfilled, (state, action) => {
        state.status = "succeeded";
        // Check if the response is empty or undefined
        if (!action.payload || action.payload.length === 0) {
          state.attractions = [];
          state.filteredAttractions = [];
        } else {
          state.attractions = action.payload;
          state.filteredAttractions = action.payload;
        }
      })
      .addCase(fetchAttractions.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload;
      })
      .addCase(fetchAttractionDetails.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchAttractionDetails.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.attractionDetails = action.payload;
      })
      .addCase(fetchAttractionDetails.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload;
      })
      .addCase(createBooking.pending, (state) => {
        state.status = "loading";
      })
      .addCase(createBooking.fulfilled, (state, action) => {
        state.status = "succeeded";
        // console.log('Attraction Booking', action.payload);

        state.checkoutData = action.payload;
      })
      .addCase(createBooking.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload;
      });
  },
});

// Export actions
export const {
  setFilters,
  clearFilters,
  setSearchText,
  setDateRange,
  setSelectedAttraction,
  setSortBy,
  setCheckoutData,
  setAttractionService,
  setSearchParams,
  setSearchFilter,
  setSelectedModeData,
  addAttractionBookings,
  clearAttractions,
} = attractionsSlice.actions;

// Export selectors
export const selectAttractions = (state) => state.attractions.attractions;
export const selectAttractionDetails = (state) =>
  state.attractions.attractionDetails;
export const selectFilteredAttractions = (state) =>
  state.attractions.filteredAttractions;
export const selectFilters = (state) => state.attractions.filters;

export default attractionsSlice.reducer;

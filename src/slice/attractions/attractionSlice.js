import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { setUserInfo, setBookingResponse } from "../common/customerInfo";
import { setDateService } from "../common/dateServicesSlice";
import { BASE_URL } from "@/services/api";
import { selectDmcId } from "../dmc/dmcSlice";
import { updateServiceResponse } from "../common/stepperButtonSlice";

// Async thunk to fetch attractions
export const fetchAttractions = createAsyncThunk(
  "attractions/fetchAttractions",
  async (
    { city, date, adults, children, tour_id, selectedDate, fromMainSearch, start , limit  },
    { rejectWithValue, dispatch, getState }
  ) => {
    try {
      // If the search is coming from MainFilterSearchBox, return empty array
      // if (fromMainSearch) {
      //   return [];
      // }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // Get selected DMC ID from Redux state
      const state = getState();
      const selectedDmcId = selectDmcId(state);
      // console.log('🎯 AttractionsSlice - Fetching attractions with DMC ID:', selectedDmcId);

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
      
      // Add pagination parameters
      if (start) queryParams.append("start", start);
      if (limit) queryParams.append("limit", limit);
      
      // Add DMC ID to query parameters if available
      if (selectedDmcId) {
        queryParams.append("dmc_id", selectedDmcId);
      }

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
    { rejectWithValue, dispatch, getState }
  ) => {
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // Get selected DMC ID from Redux state
      const state = getState();
      const selectedDmcId = selectDmcId(state);
      
      // Use selected DMC ID from Redux if available, otherwise use the passed dmc_id
      const finalDmcId = selectedDmcId || dmc_id;
      
      // Construct the API URL with the mode and dmc_id parameters
      const mode = price_mode || "default_value";
      // console.log('Fetching attraction details with params:', { attractionId, mode, dmc_id: finalDmcId });
      // console.log('Selected DMC ID from Redux:', selectedDmcId);
      
      const apiUrl = `${BASE_URL}/attraction-details?attractionId=${attractionId}&mode=${mode}&dmc_id=${finalDmcId}`;

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
      // console.log('🎯 AttractionsSlice - Selected DMC ID from Redux:', selectedDmcId);

      // Add selected DMC ID to booking details
      const updatedBookingDetails = {
        ...bookingDetails,
        data: bookingDetails.data.map(item => ({
          ...item,
          dmc_id: selectedDmcId // Use selected DMC ID from Redux store
        }))
      };

      // console.log('🚀 AttractionsSlice - Booking with DMC ID:', selectedDmcId);

      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)" ||
        userRole === "DMC Assistant Operational Manager" ||
        userRole === "DMC Operational Manager" ||
        userRole === "Operational Head(DMC)"
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

      // Update stepper button visibility based on booking response
      dispatch(updateServiceResponse({ 
        service: 'attraction', 
        response: response.data 
      }));

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
  isFromMainSearch: false, // Add flag to track if search came from MainFilterSearchBox
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
      // console.log('AttractionService', action.payload);
      state.services = action.payload;
    },
    setSearchParams: (state, action) => {
      const serializedPayload = {
        ...action.payload,
        date: action.payload.date && action.payload.date.format
          ? action.payload.date.format("YYYY-MM-DD")
          : action.payload.date,
        selectedDate: action.payload.selectedDate && action.payload.selectedDate.format
          ? action.payload.selectedDate.format("YYYY-MM-DD")
          : action.payload.selectedDate,
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
    setIsFromMainSearch: (state, action) => {
      state.isFromMainSearch = action.payload;
    },
    resetIsFromMainSearch: (state) => {
      state.isFromMainSearch = false;
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
        
        // Check if this is from MainFilterSearchBox (fromMainSearch: true)
        const isFromMainSearch = action.meta.arg.fromMainSearch;
        
        // If fromMainSearch is true, always clear the data
        if (isFromMainSearch) {
          state.attractions = [];
          state.filteredAttractions = [];
          state.isFromMainSearch = true; // Set the flag
          return;
        }
        
        // Get pagination parameters
        const { start = 0 } = action.meta.arg;
        const isFirstPage = start === 0;
        
        // Handle the response
        if (!action.payload) {
          // If payload is null/undefined, treat as empty array
          action.payload = [];
        }
        
        if (isFirstPage) {
          // First page: replace existing data
          state.attractions = action.payload;
          state.filteredAttractions = action.payload;
        } else {
          // Subsequent pages: append to existing data
          if (Array.isArray(action.payload)) {
            const existingIds = new Set(state.attractions.map(attraction => attraction.id));
            const newAttractions = action.payload.filter(attraction => !existingIds.has(attraction.id));
            state.attractions = [...state.attractions, ...newAttractions];
            state.filteredAttractions = [...state.filteredAttractions, ...newAttractions];
          }
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
  setIsFromMainSearch,
  resetIsFromMainSearch,
} = attractionsSlice.actions;

// Export selectors
export const selectAttractions = (state) => state.attractions.attractions;
export const selectAttractionDetails = (state) =>
  state.attractions.attractionDetails;
export const selectFilteredAttractions = (state) =>
  state.attractions.filteredAttractions;
export const selectFilters = (state) => state.attractions.filters;

export default attractionsSlice.reducer;




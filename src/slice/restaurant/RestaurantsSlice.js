import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL } from "@/services/api";
import { selectDmcId } from "../dmc/dmcSlice";

export const fetchRestaurants = createAsyncThunk(
  "restaurants/fetchRestaurants",
  async (
    { city, date, adults, children, tour_id, fromMainSearch },
    { rejectWithValue, dispatch, getState }
  ) => {
    try {
      // If the search is coming from MainFilterSearchBox, return empty array
      if (fromMainSearch) {
        return [];
      }

      // Store the search parameters in Redux
      dispatch(setSearchParams({
        location: city,
        date: date,
        adults: adults,
        children: children
      }));

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // Get selected DMC ID from Redux state
      const state = getState();
      const selectedDmcId = selectDmcId(state);
      console.log('🎯 RestaurantsSlice - Fetching restaurants with DMC ID:', selectedDmcId);

      const queryParams = new URLSearchParams();

      if (city) queryParams.append("city", city);

      // Format date as expected by the backend
      if (date && date !== "") {
        const formattedDate = JSON.stringify({ 0: date });
        queryParams.append("date", formattedDate);
      }

      if (adults) queryParams.append("adults", adults);
      if (children) queryParams.append("children", children);
      if (tour_id) queryParams.append("tour_id", tour_id);
      
      // Add DMC ID to query parameters if available
      if (selectedDmcId) {
        queryParams.append("dmc_id", selectedDmcId);
      }

      // console.log('Query params:', queryParams.toString());
      const response = await axios.get(
        `${BASE_URL}/restaurant?${queryParams.toString()}`,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
          },
        }
      );

      console.log('Restaurant API response:', response.data);
      return response.data;
    } catch (error) {
      console.error("Error fetching restaurants:", error);
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const fetchRestaurantsDetails = createAsyncThunk(
  "restaurants/fetchRestaurantsDetails",
  async (
    { restaurantId, price_mode, dmc_id },
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
      const mode = price_mode || "dmc";
      console.log('Fetching restaurant details with params:', { restaurantId, mode, dmc_id: finalDmcId });
      console.log('Selected DMC ID from Redux:', selectedDmcId);
      
      const response = await axios.get(
        `${BASE_URL}/restaurant-details?restaurantId=${restaurantId}&mode=${mode}&dmc_id=${finalDmcId}`,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
          },
        }
      );

      console.log("Fetched Restaurant Details Response:", response.data);
      return response.data;
    } catch (error) {
      console.error("Error fetching restaurant details:", error);
      if (error.response?.status === 401) {
        await dispatch(logoutUser());
        dispatch(setAuthenticated(false));
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const createBooking = createAsyncThunk(
  "restaurants/createBooking",
  async (bookingDetails, { rejectWithValue, getState, dispatch }) => {
    try {
      const state = getState();
      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;

      // Get selected DMC ID from Redux state
      const selectedDmcId = selectDmcId(state);
      console.log('🎯 RestaurantsSlice - Selected DMC ID from Redux:', selectedDmcId);

      // Add selected DMC ID to booking details
      const updatedBookingDetails = {
        ...bookingDetails,
        data: bookingDetails.data.map(item => ({
          ...item,
          dmc_id: selectedDmcId // Use selected DMC ID from Redux store
        }))
      };

      console.log('🚀 RestaurantsSlice - Booking with DMC ID:', selectedDmcId);

      // Corrected conditional statement
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

      // Extract user info from the first booking data item
      if (bookingDetails.data && bookingDetails.data[0]) {
        const userInfo = {
          fullName: bookingDetails.data[0].fullName,
          email: bookingDetails.data[0].email,
          phone: bookingDetails.data[0].phone,
          countryCode: bookingDetails.data[0].countryCode,
          address1: bookingDetails.data[0].address1,
          address2: bookingDetails.data[0].address2,
          state: bookingDetails.data[0].state,
          zip: bookingDetails.data[0].zip,
          specialRequests: bookingDetails.data[0].specialRequests,
        };

        dispatch(storeUserInfo(userInfo));
      }

      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const initialState = {
  restaurants: [],
  filters: {
    category: "All",
    priceMode: "dmc",
    priceRange: { min: 1, max: 10000 },
    cuisines: [],
  },
  highestPrice: 1000,
  searchText: "",
  sortBy: "name",
  selectedRestaurant: null, //Details
  status: "idle", // Track loading status
  error: null, // To store any error
  checkoutData: null,
  bookings: [],
  services: [],
  searchParams: {
    location: null,
    date: "",
    adults: 1,
    children: 0,
  },
  restaurantDetails: null,
  modeMap: {},
  restaurantBookings: [],
  // Initialize userInfo from localStorage
  userInfo: null,
};

const restaurantsSlice = createSlice({
  name: "restaurants",
  initialState,
  reducers: {
    addRestaurantBooking: (state, action) => {
      // console.log('Received booking:', action.payload);
      state.restaurantBookings = [action.payload]; // Replace with the latest booking
    },

    setHighestTotalPrice: (state, action) => {
      state.filters.priceRange.max = action.payload;
      state.highestPrice = action.payload;
    },

    setFilters(state, action) {
      // console.log("setFilters action triggered with payload:", action.payload);
      state.filters = { ...state.filters, ...action.payload };
    },
    setSortBy(state, action) {
      state.sortBy = action.payload;
    },
    setSelectedRestaurant(state, action) {
      state.selectedRestaurant = action.payload;
    },
    setSearchText(state, action) {
      state.searchText = action.payload;
    },
    setCheckoutData(state, action) {
      state.checkoutData = action.payload;
      //console.log("Updated Restaurants Redux State - checkoutData:", state.checkoutData);
    },
    setRestaurantsService: (state, action) => {
      // console.log("Setting Restaurants service data:", action.payload);
      state.services = action.payload; // Update the state with the new data
    },
    setSearchParams(state, action) {
      state.searchParams = {
        ...state.searchParams,
        ...action.payload,
        date: action.payload.date || "",
      };
    },
    updateModeMap: (state, action) => {
      const { restaurantId, mode = "dmc", prices } = action.payload;

      // If no existing mode, default to "dmc"
      if (!state.modeMap[restaurantId]) {
        state.modeMap[restaurantId] = { mode: "dmc", prices: prices || {} };
        // console.log(`Set default mode to "dmc" for restaurantId: ${restaurantId}`);
      } else {
        state.modeMap[restaurantId] = { mode, prices };
        // console.log(`Updated modeMap for restaurantId: ${restaurantId}, Mode: ${mode}, Prices:`, prices);
      }
    },

    storeUserInfo: (state, action) => {
      state.userInfo = action.payload;
      // Also store in localStorage for persistence
      localStorage.setItem(
        "lastRestaurantUserInfo",
        JSON.stringify(action.payload)
      );
    },
    clearRestaurants: (state) => {
      state.restaurants = [];
      // Don't clear searchParams as we want to keep the last search criteria
    },
  },
  extraReducers: (builder) => {
    builder
      // Handle fetchRestaurants actions
      .addCase(fetchRestaurants.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchRestaurants.fulfilled, (state, action) => {
        state.status = "succeeded";
        // Check if the response is empty or undefined
        if (!action.payload || action.payload.length === 0) {
          console.log('No restaurants found in response');
          state.restaurants = [];
        } else {
          console.log('Setting restaurants in state:', action.payload);
          state.restaurants = action.payload;
        }
      })
      .addCase(fetchRestaurants.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.error.message;
      })

      // Handle createBooking actions
      .addCase(createBooking.pending, (state) => {
        state.status = "loading";
      })
      .addCase(createBooking.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.checkoutData = action.payload;

        // Parse and store user info from the response if available
        if (action.payload?.order?.data) {
          try {
            const rawData = action.payload.order.data;
            let parsedData =
              typeof rawData === "string" ? JSON.parse(rawData) : rawData;

            if (Array.isArray(parsedData) && parsedData.length > 0) {
              const userInfo = {
                fullName: parsedData[0].fullName || "",
                email: parsedData[0].email || "",
                phone: parsedData[0].phone || "",
                countryCode: parsedData[0].countryCode || "+1",
                address1: parsedData[0].address1 || "",
                address2: parsedData[0].address2 || "",
                state: parsedData[0].state || "",
                zip: parsedData[0].zip || "",
                specialRequests: parsedData[0].specialRequests || "",
              };

              // Update both Redux state and localStorage
              state.userInfo = userInfo;
              localStorage.setItem(
                "lastRestaurantUserInfo",
                JSON.stringify(userInfo)
              );
              // console.log('User info updated from booking response:', userInfo);
            }
          } catch (error) {
            console.error("Error storing user info from response:", error);
          }
        }

        // Store booking info separately
        if (action.payload?.order?.tour_id) {
          state.bookings.push({
            bookingDate: action.payload.order.bookingDate,
            tourId: action.payload.order.tour_id,
          });
        }
      })

      .addCase(createBooking.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload; // Store the error message
      })

      // Handle fetchRestaurantsDetails actions
      .addCase(fetchRestaurantsDetails.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchRestaurantsDetails.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.selectedRestaurant = action.payload; // Store fetched restaurant details in selectedRestaurant
        state.restaurantDetails = action.payload; // Also store in restaurantDetails for backward compatibility
      })
      .addCase(fetchRestaurantsDetails.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload;
      });
  },
});

export const {
  setFilters,
  setSortBy,
  setSelectedRestaurant,
  setSearchText,
  setCheckoutData,
  updateModeMap,
  setRestaurantsService,
  setSearchParams,
  setHighestTotalPrice,
  addRestaurantBooking,
  storeUserInfo,
  clearRestaurants,
} = restaurantsSlice.actions;

// Selectors
export const selectRestaurants = (state) => state.restaurants.restaurants;
export const selectFilters = (state) => state.restaurants.filters;
export const selectSortBy = (state) => state.restaurants.sortBy;
export const selectSearchText = (state) => state.restaurants.searchText;
export const selectSelectedRestaurant = (state) =>
  state.restaurants.selectedRestaurant;
export const selectStatus = (state) => state.restaurants.status;
export const selectError = (state) => state.restaurants.error;
export const selectUserInfo = (state) => state.restaurants.userInfo;

export default restaurantsSlice.reducer;

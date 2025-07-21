import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from 'js-cookie';

// Import only the BASE_URL from api.js
import { BASE_URL } from '../../services/api';
// Import store as named export
import { store } from '../../store/store';

// Create an axios instance with default configuration
const api = axios.create({
  baseURL: BASE_URL,
});

// Add request interceptor to automatically include auth headers
api.interceptors.request.use(
  (config) => {
    const authToken = Cookies.get("authToken");
    const reduxState = store.getState();
    const agentID = reduxState.editing?.agentId;
    const userRole = reduxState.auth?.userRole;
    const cookieAgentId = Cookies.get("AgentId");
    
    console.log("Interceptor - Auth token:", authToken);
    console.log("Interceptor - Redux agentID:", agentID);
    console.log("Interceptor - User role:", userRole);
    console.log("Interceptor - Cookie AgentId:", cookieAgentId);

    // Determine which agent ID to use
    let AgentId;
    if (
      userRole === "Sales Head(DMC)" ||
      userRole === "Sales Manager (DMC)" ||
      userRole === "Assistant Manager (DMC)"
    ) {
      // For managers, use the selected agent ID if available
      AgentId = agentID || cookieAgentId;
    } else {
      // For regular agents, use their own ID
      AgentId = cookieAgentId;
    }

    console.log("Interceptor - Final AgentId to use:", AgentId);

    // Add Authorization header if token exists
    if (authToken) {
      config.headers.Authorization = `Bearer ${authToken}`;
    }

    // Add agent-id header if AgentId exists
    if (AgentId) {
      // Set both formats to be safe
      config.headers["agent-id"] = AgentId;
      
      console.log("Interceptor - Headers set:", config.headers);
    } else {
      console.warn("Interceptor - No AgentId available for header");
    }

    return config;
  },
  (error) => {
    console.error("Interceptor request error:", error);
    return Promise.reject(error);
  }
);

// Utility function to transform parameter names from underscore to hyphen format
const transformParams = (params) => {
  if (!params) return params;

  const transformed = {};
  const hyphenParams = [
    { from: "dmc_id", to: "dmc-id" },
    { from: "price_mode", to: "price-mode" },
    { from: "agent_id", to: "agent-id" },
    { from: "tour_id", to: "tour-id" },
  ];

  Object.keys(params).forEach((key) => {
    const paramToTransform = hyphenParams.find((param) => param.from === key);
    if (paramToTransform) {
      transformed[paramToTransform.to] = params[key];
    } else {
      transformed[key] = params[key];
    }
  });

  console.log("Transformed params:", transformed);
  return transformed;
};

// Package API endpoints
const packageAPI = {
  fetchPackages: (params) =>
    api.get("/packages", { params: transformParams(params) }),
  fetchPackageDetails: (params) =>
    api.get("/package-details", { params: transformParams(params) }),
  packageBooking: (data) => 
    api.post("/package-booking", data),
  cancelPackageBooking: (booking_id) =>
    api.post("cancel-package-booking", { booking_id }),
  fetchPackageBookingLists: (params) =>
    api.get("package-booking-lists", { params: transformParams(params) }),
};

// Async thunk for fetching packages
export const fetchPackages = createAsyncThunk(
  'prePackages/fetchPackages',
  async (searchParams, { rejectWithValue }) => {
    try {
      const response = await packageAPI.fetchPackages(searchParams);
      return response.data;
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Failed to fetch packages'
      );
    }
  }
);

// Async thunk for fetching package details
export const fetchPackageDetails = createAsyncThunk(
  'prePackages/fetchPackageDetails',
  async (packageId, { rejectWithValue, getState }) => {
    try {
      // Get the searchParams from state
      const state = getState();
      const searchParams = state.prePackages.searchParams;
      
      // Fetch the package details
      const response = await packageAPI.fetchPackageDetails({ 
        package_id: packageId,
        // Include arrival_date if available from searchParams
        ...(searchParams?.arrival_date && { arrival_date: searchParams.arrival_date })
      });
      
      // Merge the arrival_date from searchParams into the response data if available
      const packageDetails = response.data;
      if (searchParams?.arrival_date) {
        return {
          ...packageDetails,
          arrival_date: searchParams.arrival_date
        };
      }
      
      return packageDetails;
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Failed to fetch package details'
      );
    }
  }
);

// Async thunk for booking a package
export const bookPackage = createAsyncThunk(
  'prePackages/bookPackage',
  async (bookingData, { rejectWithValue }) => {
    try {
      const response = await packageAPI.packageBooking(bookingData);
      return response.data;
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Failed to book package'
      );
    }
  }
);

// Async thunk for canceling a package booking
export const cancelPackageBooking = createAsyncThunk(
  'prePackages/cancelPackageBooking',
  async (booking_id, { rejectWithValue }) => {
    try {
      console.log("Canceling package booking with ID:", booking_id);
      const response = await packageAPI.cancelPackageBooking(booking_id);
      console.log("API Response for cancel-package-booking:", response);
      return { booking_id, ...response.data };
    } catch (error) {
      console.error("Error canceling package booking:", error);
      return rejectWithValue(
        error.response?.data?.message || 'Failed to cancel package booking'
      );
    }
  }
);

// Async thunk for fetching package booking lists
export const fetchPackageBookingLists = createAsyncThunk(
  'prePackages/fetchPackageBookingLists',
  async (params, { rejectWithValue }) => {
    try {
      console.log("Fetching package booking lists with params:", params);
      const response = await packageAPI.fetchPackageBookingLists(params);
      console.log("API Response for package-booking-lists:", response);
      return response.data;
    } catch (error) {
      console.error("Error fetching package booking lists:", error);
      return rejectWithValue(
        error.response?.data?.message || 'Failed to fetch package booking lists'
      );
    }
  }
);

const initialState = {
  packages: [],
  loading: false,
  error: null,
  searchParams: null,
  packageDetails: null,
  loadingDetails: false,
  errorDetails: null,
  bookingLoading: false,
  bookingSuccess: false,
  bookingError: null,
  bookingData: null,
  // New state for package booking lists
  bookingLists: [],
  bookingListsLoading: false,
  bookingListsError: null,
  // New state for package booking cancellation
  cancelBookingLoading: false,
  cancelBookingSuccess: false,
  cancelBookingError: null,
};

const prePackagesSlice = createSlice({
  name: 'prePackages',
  initialState,
  reducers: {
    setSearchParams: (state, action) => {
      console.log("action.payload", action.payload);
      state.searchParams = action.payload;
    },
    resetPackages: (state) => {
      state.packages = [];
      state.error = null;
    },
    resetPackageDetails: (state) => {
      state.packageDetails = null;
      state.errorDetails = null;
    },
    resetBookingStatus: (state) => {
      state.bookingSuccess = false;
      state.bookingError = null;
      state.bookingData = null;
    },
    resetBookingLists: (state) => {
      state.bookingLists = [];
      state.bookingListsError = null;
    },
    resetCancelBookingStatus: (state) => {
      state.cancelBookingSuccess = false;
      state.cancelBookingError = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchPackages.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPackages.fulfilled, (state, action) => {
        state.loading = false;
        state.packages = action.payload;
      })
      .addCase(fetchPackages.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchPackageDetails.pending, (state) => {
        state.loadingDetails = true;
        state.errorDetails = null;
      })
      .addCase(fetchPackageDetails.fulfilled, (state, action) => {
        state.loadingDetails = false;
        state.packageDetails = action.payload;
      })
      .addCase(fetchPackageDetails.rejected, (state, action) => {
        state.loadingDetails = false;
        state.errorDetails = action.payload;
      })
      // Handle package booking states
      .addCase(bookPackage.pending, (state) => {
        state.bookingLoading = true;
        state.bookingSuccess = false;
        state.bookingError = null;
      })
      .addCase(bookPackage.fulfilled, (state, action) => {
        state.bookingLoading = false;
        state.bookingSuccess = true;
        state.bookingData = action.payload;
      })
      .addCase(bookPackage.rejected, (state, action) => {
        state.bookingLoading = false;
        state.bookingError = action.payload;
      })
      // Handle package booking cancellation states
      .addCase(cancelPackageBooking.pending, (state) => {
        state.cancelBookingLoading = true;
        state.cancelBookingSuccess = false;
        state.cancelBookingError = null;
      })
      .addCase(cancelPackageBooking.fulfilled, (state, action) => {
        state.cancelBookingLoading = false;
        state.cancelBookingSuccess = true;
        
        // Update the booking status in the bookingLists array if it exists
        if (state.bookingLists && Array.isArray(state.bookingLists)) {
          state.bookingLists = state.bookingLists.map(booking => {
            if ((booking.booking_id && booking.booking_id === action.payload.booking_id) || 
                (booking.id && booking.id === action.payload.booking_id)) {
              return { ...booking, status: 4 };
            }
            return booking;
          });
        }
      })
      .addCase(cancelPackageBooking.rejected, (state, action) => {
        state.cancelBookingLoading = false;
        state.cancelBookingError = action.payload;
      })
      // Handle package booking lists states
      .addCase(fetchPackageBookingLists.pending, (state) => {
        state.bookingListsLoading = true;
        state.bookingListsError = null;
      })
      .addCase(fetchPackageBookingLists.fulfilled, (state, action) => {
        state.bookingListsLoading = false;
        
        // Check if the response contains a 'booking_lists' property
        if (action.payload && typeof action.payload === 'object') {
          console.log("Package booking lists response payload:", action.payload);
          
          // Handle different response formats
          if (Array.isArray(action.payload)) {
            console.log("Response is a direct array");
            state.bookingLists = action.payload;
          } else if (action.payload.booking_lists && Array.isArray(action.payload.booking_lists)) {
            console.log("Response contains booking_lists array");
            state.bookingLists = action.payload.booking_lists;
          } else if (action.payload.data && Array.isArray(action.payload.data)) {
            console.log("Response contains data array");
            state.bookingLists = action.payload.data;
          } else {
            console.log("Response has unknown format, setting empty array");
            state.bookingLists = [];
          }
        } else {
          console.log("No valid payload in response");
          state.bookingLists = [];
        }
      })
      .addCase(fetchPackageBookingLists.rejected, (state, action) => {
        state.bookingListsLoading = false;
        state.bookingLists = [];
        state.bookingListsError = action.payload;
        console.error("Failed to fetch booking lists:", action.payload);
      });
  },
});

export const { 
  setSearchParams, 
  resetPackages, 
  resetPackageDetails, 
  resetBookingStatus, 
  resetBookingLists,
  resetCancelBookingStatus 
} = prePackagesSlice.actions;

export default prePackagesSlice.reducer;

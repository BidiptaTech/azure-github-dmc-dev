import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from 'js-cookie';

// Import only the BASE_URL from api.js
import { BASE_URL } from '../../services/api';
// Import store as named export
import { store } from '../../store/store';
import { selectDmcId } from '../dmc/dmcSlice';

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
    
    // console.log("Interceptor - Auth token:", authToken);
    // console.log("Interceptor - Redux agentID:", agentID);
    // console.log("Interceptor - User role:", userRole);
    // console.log("Interceptor - Cookie AgentId:", cookieAgentId);

    // Determine which agent ID to use
    let AgentId;
    if (
      userRole === "Sales Head(DMC)" ||
      userRole === "Sales Manager (DMC)" ||
      userRole === "Assistant Manager (DMC)" ||
      userRole === "Operational Head(DMC)" ||
      userRole === "DMC Operational Manager" ||
      userRole === "DMC Assistant Operational Manager" 
      
    ) {
      // For managers, use the selected agent ID if available
      AgentId = agentID || cookieAgentId;
    } else {
      // For regular agents, use their own ID
      AgentId = cookieAgentId;
    }

    // console.log("Interceptor - Final AgentId to use:", AgentId);

    // Add Authorization header if token exists
    if (authToken) {
      config.headers.Authorization = `Bearer ${authToken}`;
    }

    // Add agent-id header if AgentId exists
    if (AgentId) {
      // Set both formats to be safe
      config.headers["agent-id"] = AgentId;
      
      // console.log("Interceptor - Headers set:", config.headers);
    } else {
      // console.warn("Interceptor - No AgentId available for header");
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
    { from: "dmc_id", to: "dmc_id" },
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
  async ({ searchParams, start = 0, limit = 5 }, { rejectWithValue, getState }) => {
    try {
      // Get selected DMC ID from Redux state
      const state = getState();
      const selectedDmcId = selectDmcId(state);
      // console.log('🎯 PrePackagesSlice - Fetching packages with DMC ID:', selectedDmcId);
      // console.log('🎯 PrePackagesSlice - Pagination params:', { start, limit });

      // Add DMC ID and pagination parameters to search parameters
      const updatedSearchParams = {
        ...searchParams,
        start,
        limit,
        ...(selectedDmcId && { dmc_id: selectedDmcId })
      };

      console.log('🎯 PrePackagesSlice - Updated search params:', updatedSearchParams);

      const response = await packageAPI.fetchPackages(updatedSearchParams);
      // console.log('🎯 PrePackagesSlice - API response:', response);
      // console.log('🎯 PrePackagesSlice - API response data:', response.data);
      return response.data;
    } catch (error) {
      console.error('🎯 PrePackagesSlice - API error:', error);
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
      
      // Get selected DMC ID from Redux state
      const selectedDmcId = selectDmcId(state);
      // console.log('🎯 PrePackagesSlice - Fetching package details with DMC ID:', selectedDmcId);
      
      // Prepare parameters for package details API
      const params = { 
        package_id: packageId,
        // Include arrival_date if available from searchParams
        ...(searchParams?.arrival_date && { arrival_date: searchParams.arrival_date }),
        // Add DMC ID if available
        ...(selectedDmcId && { dmc_id: selectedDmcId })
      };
      
      // console.log('🎯 PrePackagesSlice - Package details params:', params);
      
      // Fetch the package details
      const response = await packageAPI.fetchPackageDetails(params);
      
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
  async (bookingData, { rejectWithValue, getState }) => {
    try {
      // Get selected DMC ID from Redux state
      const state = getState();
      const selectedDmcId = selectDmcId(state);
      // console.log('🎯 PrePackagesSlice - Booking package with DMC ID:', selectedDmcId);

      // Add DMC ID to booking data if available
      const updatedBookingData = {
        ...bookingData,
        ...(selectedDmcId && { dmc_id: selectedDmcId })
      };

      // console.log('🎯 PrePackagesSlice - Updated booking data:', updatedBookingData);

      const response = await packageAPI.packageBooking(updatedBookingData);
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
      // console.log("Canceling package booking with ID:", booking_id);
      const response = await packageAPI.cancelPackageBooking(booking_id);
      // console.log("API Response for cancel-package-booking:", response);
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
  async (params = {}, { rejectWithValue, getState }) => {
    try {
      // Get selected DMC ID from Redux state
      const state = getState();
      const selectedDmcId = state.dmc?.dmcId;
      
      // Default parameters
      const defaultParams = {
        start: 0,
        limit: 10,
        status: 'all', // 'all', 'ongoing', 'upcoming', 'past'
        ...params
      };

      // Add DMC ID if available
      if (selectedDmcId) {
        defaultParams.dmc_id = selectedDmcId;
      }

      // console.log("Fetching package booking lists with params:", defaultParams);
      const response = await packageAPI.fetchPackageBookingLists(defaultParams);
      // console.log("API Response for package-booking-lists:", response);
      
      // Return the response data along with the parameters used
      return {
        data: response.data,
        params: defaultParams
      };
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
  // Pagination and filtering state for booking lists
  bookingListsPagination: {
    start: 0,
    limit: 10,
    total: 0,
    hasMore: true
  },
  bookingListsStatus: 'all', // 'all', 'ongoing', 'upcoming', 'past'
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
      // console.log("action.payload", action.payload);
      state.searchParams = action.payload;
    },
    resetPackages: (state) => {
      state.packages = [];
      state.error = null;
      state.searchParams = null;
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
    // New reducers for pagination and status management
    setBookingListsStatus: (state, action) => {
      state.bookingListsStatus = action.payload;
      // Reset pagination when status changes
      state.bookingListsPagination.start = 0;
      state.bookingLists = [];
    },
    setBookingListsPagination: (state, action) => {
      state.bookingListsPagination = {
        ...state.bookingListsPagination,
        ...action.payload
      };
    },
    resetBookingListsPagination: (state) => {
      state.bookingListsPagination = {
        start: 0,
        limit: 10,
        total: 0,
        hasMore: true
      };
      state.bookingListsStatus = 'all';
      state.bookingLists = [];
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
        // console.log('🎯 PrePackagesSlice - API response payload:', action.payload);
        // console.log('🎯 PrePackagesSlice - API response type:', typeof action.payload);
        
        // Check if the response is empty or undefined
        const isEmptyResponse = 
          !action.payload || 
          (Array.isArray(action.payload) && action.payload.length === 0) ||
          (action.payload && typeof action.payload === 'object' && 
           action.payload.packages && Array.isArray(action.payload.packages) && action.payload.packages.length === 0) ||
          (action.payload && typeof action.payload === 'object' && 
           action.payload.data && Array.isArray(action.payload.data) && action.payload.data.length === 0);
        
        if (isEmptyResponse) {
          // If no data returned and we have existing data, don't clear it
          // This indicates we've reached the end of available data
          if (state.packages.length === 0) {
            state.packages = [];
          }
          // console.log('🎯 PrePackagesSlice - Empty response detected, keeping existing packages:', state.packages.length);
          // console.log('🎯 PrePackagesSlice - Response format:', action.payload);
          return;
        }
        
        // Handle different response formats
        let newPackages = [];
        if (action.payload && typeof action.payload === 'object') {
          if (Array.isArray(action.payload)) {
            // Direct array of packages
            newPackages = action.payload;
            // console.log('🎯 PrePackagesSlice - Got packages as direct array:', action.payload.length);
          } else if (action.payload.packages && Array.isArray(action.payload.packages)) {
            // Object with packages property
            newPackages = action.payload.packages;
            // console.log('🎯 PrePackagesSlice - Got packages from packages property:', action.payload.packages.length);
          } else if (action.payload.data && Array.isArray(action.payload.data)) {
            // Object with data property
            newPackages = action.payload.data;
            // console.log('🎯 PrePackagesSlice - Got packages from data property:', action.payload.data.length);
          } else {
            // console.log('🎯 PrePackagesSlice - Unknown response format, setting empty array');
            newPackages = [];
          }
        } else {
          // console.log('🎯 PrePackagesSlice - No valid payload, setting empty array');
          newPackages = [];
        }
        
        // For infinite scroll: append new data to existing data
        // Check if this is the first page (start = 0) or subsequent pages
        const isFirstPage = action.meta.arg.start === 0;
        
        if (isFirstPage) {
          // First page: replace existing data
          state.packages = newPackages;
          // console.log('🎯 PrePackagesSlice - Setting packages (first page):', newPackages.length);
        } else {
          // Subsequent pages: append to existing data
          state.packages = [...state.packages, ...newPackages];
          // console.log('🎯 PrePackagesSlice - Appending packages:', newPackages.length, 'Total:', state.packages.length);
        }
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
              return { ...booking, status: 7 };
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
        
        const { data: responseData, params } = action.payload;
        // console.log("Package booking lists response payload:", responseData);
        // console.log("Request params:", params);
        
        // Update pagination state
        if (params) {
          state.bookingListsPagination.start = params.start;
          state.bookingListsPagination.limit = params.limit;
          state.bookingListsStatus = params.status;
        }
        
        // Handle different response formats
        let bookingData = [];
        let totalCount = 0;
        
        if (responseData && typeof responseData === 'object') {
          if (Array.isArray(responseData)) {
            // console.log("Response is a direct array");
            bookingData = responseData;
            totalCount = responseData.length;
          } else if (responseData.booking_lists && Array.isArray(responseData.booking_lists)) {
            // console.log("Response contains booking_lists array");
            bookingData = responseData.booking_lists;
            totalCount = responseData.total || responseData.booking_lists.length;
          } else if (responseData.data && Array.isArray(responseData.data)) {
            // console.log("Response contains data array");
            bookingData = responseData.data;
            totalCount = responseData.total || responseData.data.length;
          } else {
            // console.log("Response has unknown format, setting empty array");
            bookingData = [];
            totalCount = 0;
          }
        } else {
          //  console.log("No valid payload in response");
          bookingData = [];
          totalCount = 0;
        }
        
        // Update booking lists and pagination
        if (params && params.start === 0) {
          // First page or reset - replace the data
          state.bookingLists = bookingData;
        } else {
          // Subsequent pages - append the data
          state.bookingLists = [...state.bookingLists, ...bookingData];
        }
        
        // Update pagination info
        state.bookingListsPagination.total = totalCount;
        // hasMore should be true if we received a full page of data (indicating there might be more)
        state.bookingListsPagination.hasMore = bookingData.length === params?.limit;
      })
      .addCase(fetchPackageBookingLists.rejected, (state, action) => {
        state.bookingListsLoading = false;
        state.bookingLists = [];
        state.bookingListsError = action.payload;
        // console.error("Failed to fetch booking lists:", action.payload);
      });
  },
});

export const { 
  setSearchParams, 
  resetPackages, 
  resetPackageDetails, 
  resetBookingStatus, 
  resetBookingLists,
  resetCancelBookingStatus,
  setBookingListsStatus,
  setBookingListsPagination,
  resetBookingListsPagination
} = prePackagesSlice.actions;

export default prePackagesSlice.reducer;

import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { endpoints } from '../../services/api';

// Async thunk for fetching packages
export const fetchPackages = createAsyncThunk(
  'prePackages/fetchPackages',
  async (searchParams, { rejectWithValue }) => {
    try {
      const response = await endpoints.fetchPackages(searchParams);
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
      const response = await endpoints.fetchPackageDetails({ 
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
      const response = await endpoints.packageBooking(bookingData);
      return response.data;
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Failed to book package'
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
      const response = await endpoints.request('get', 'package-booking-lists', null, { params });
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

export const { setSearchParams, resetPackages, resetPackageDetails, resetBookingStatus, resetBookingLists } = prePackagesSlice.actions;
export default prePackagesSlice.reducer;

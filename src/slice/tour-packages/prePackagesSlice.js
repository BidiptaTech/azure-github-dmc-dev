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
  async (packageId, { rejectWithValue }) => {
    try {
      const response = await endpoints.fetchPackageDetails({ package_id: packageId });
      return response.data;
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
      });
  },
});

export const { setSearchParams, resetPackages, resetPackageDetails, resetBookingStatus } = prePackagesSlice.actions;
export default prePackagesSlice.reducer;

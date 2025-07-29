import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from 'js-cookie';
import { BASE_URL } from '../../services/api';

// Async thunk for fetching DMCs by country
export const fetchDMCsByCountry = createAsyncThunk(
  'dmc/fetchDMCsByCountry',
  async (countries, { rejectWithValue }) => {
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken) {
        throw new Error("No auth token found");
      }

      const headers = {
        Authorization: `Bearer ${authToken}`,
      };

      if (AgentId) {
        headers["agent-id"] = AgentId;
      }

      // Convert countries array to JSON string format
      const countryParam = JSON.stringify(countries);
      
      const response = await axios.get(`${BASE_URL}/get-dmcs`, {
        headers,
        params: {
          country: countryParam
        }
      });

      return response.data;
    } catch (error) {
      console.error('Error fetching DMCs:', error);
      return rejectWithValue(
        error.response?.data?.message || error.message || 'Failed to fetch DMCs'
      );
    }
  }
);

// Initial state
const initialState = {
  dmcs: [],
  loading: false,
  error: null,
  selectedCountries: [],
  lastFetchedCountries: null,
  dmcId: null, // Selected DMC ID
  selectedDmcData: null, // Full selected DMC data
};

// DMC slice
const dmcSlice = createSlice({
  name: 'dmc',
  initialState,
  reducers: {
    // Set selected DMC ID
    setSelectedDmcId: (state, action) => {
      const dmcId = action.payload.dmcId;
      console.log('🏪 Redux: Storing selected DMC ID:', dmcId === null ? 'null (dmcId was 0)' : dmcId);
      console.log('🏪 Redux: Storing DMC Data:', action.payload.dmcData);
      
      state.dmcId = dmcId;
      state.selectedDmcData = action.payload.dmcData || null;
      
      console.log('🏪 Redux: Updated state - dmcId:', state.dmcId);
    },

    // Clear selected DMC
    clearSelectedDmc: (state) => {
      console.log('🗑️ Redux: Clearing DMC selection - setting dmcId to null');
      
      state.dmcId = null;
      state.selectedDmcData = null;
      
      console.log('🗑️ Redux: DMC selection cleared - dmcId is now null');
    },

    // Clear DMCs
    clearDMCs: (state) => {
      state.dmcs = [];
      state.error = null;
      state.selectedCountries = [];
      state.lastFetchedCountries = null;
    },
    
    // Set selected countries
    setSelectedCountries: (state, action) => {
      state.selectedCountries = action.payload;
    },
    
    // Clear error
    clearError: (state) => {
      state.error = null;
    },
    
    // Reset DMC state
    resetDMCState: (state) => {
      return initialState;
    }
  },
  extraReducers: (builder) => {
    builder
      // Fetch DMCs by country - pending
      .addCase(fetchDMCsByCountry.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      // Fetch DMCs by country - fulfilled
      .addCase(fetchDMCsByCountry.fulfilled, (state, action) => {
        state.loading = false;
        state.dmcs = action.payload;
        state.error = null;
        state.lastFetchedCountries = state.selectedCountries;
      })
      // Fetch DMCs by country - rejected
      .addCase(fetchDMCsByCountry.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
        state.dmcs = [];
      });
  },
});

// Export actions
export const {
  setSelectedDmcId,
  clearSelectedDmc,
  clearDMCs,
  setSelectedCountries,
  clearError,
  resetDMCState
} = dmcSlice.actions;

// Selectors
export const selectDMCs = (state) => state.dmc.dmcs;
export const selectDMCLoading = (state) => state.dmc.loading;
export const selectDMCError = (state) => state.dmc.error;
export const selectSelectedCountries = (state) => state.dmc.selectedCountries;
export const selectLastFetchedCountries = (state) => state.dmc.lastFetchedCountries;
export const selectDmcId = (state) => state.dmc.dmcId;
export const selectSelectedDmcData = (state) => state.dmc.selectedDmcData;

// Export reducer
export default dmcSlice.reducer; 
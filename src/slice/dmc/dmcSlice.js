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

// Async thunk for fetching DMC count
export const fetchDMCCount = createAsyncThunk(
  'dmc/fetchDMCCount',
  async (_, { rejectWithValue }) => {
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
      
      const response = await axios.get(`${BASE_URL}/dmc-count`, {
        headers
      });

      console.log('DMC Count API Response:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error fetching DMC count:', error);
      return rejectWithValue(
        error.response?.data?.message || error.message || 'Failed to fetch DMC count'
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
  dmcId: null, // Selected DMC ID (single selection - for Book Tour)
  selectedDmcData: null, // Full selected DMC data (single selection - for Book Tour)
  // New fields for multiple DMC selection (for Book an Enquiry)
  selectedDmcIds: [], // Array of selected DMC IDs (multiple selection)
  selectedDmcsData: [], // Array of selected DMCs data (multiple selection)
  // DMC Count fields
  dmcCount: null,
  dmcCountLoading: false,
  dmcCountError: null,
};

// DMC slice
const dmcSlice = createSlice({
  name: 'dmc',
  initialState,
  reducers: {
    // Set selected DMC ID (single selection - for Book Tour)
    setSelectedDmcId: (state, action) => {
      const dmcId = action.payload.dmcId;
      console.log('🏪 Redux: Storing selected DMC ID:', dmcId === null ? 'null (dmcId was 0)' : dmcId);
      console.log('🏪 Redux: Storing DMC Data:', action.payload.dmcData);
      
      state.dmcId = dmcId;
      state.selectedDmcData = action.payload.dmcData || null;
      
      console.log('🏪 Redux: Updated state - dmcId:', state.dmcId);
    },

    // Clear selected DMC (single selection - for Book Tour)
    clearSelectedDmc: (state) => {
      console.log('🗑️ Redux: Clearing DMC selection - setting dmcId to null');
      
      state.dmcId = null;
      state.selectedDmcData = null;
      
      console.log('🗑️ Redux: DMC selection cleared - dmcId is now null');
    },

    // Set multiple selected DMC IDs (multiple selection - for Book an Enquiry)
    setSelectedDmcIds: (state, action) => {
      console.log('🏪 Redux: Storing selected DMC IDs (multiple):', action.payload.dmcIds);
      console.log('🏪 Redux: Storing DMCs Data (multiple):', action.payload.dmcsData);
      
      state.selectedDmcIds = action.payload.dmcIds || [];
      state.selectedDmcsData = action.payload.dmcsData || [];
      
      // console.log('🏪 Redux: Updated state - selectedDmcIds:', state.selectedDmcIds);
    },

    // Add DMC to multiple selection (for Book an Enquiry)
    addDmcToSelection: (state, action) => {
      const { dmcId, dmcData } = action.payload;
      console.log('➕ Redux: Adding DMC to selection - dmcId:', dmcId);
      
      // Check if DMC is already selected
      if (!state.selectedDmcIds.includes(dmcId)) {
        state.selectedDmcIds.push(dmcId);
        state.selectedDmcsData.push(dmcData);
        
        console.log('➕ Redux: DMC added to selection - current selectedDmcIds:', state.selectedDmcIds);
      } else {
        console.log('⚠️ Redux: DMC already in selection:', dmcId);
      }
    },

    // Remove DMC from multiple selection (for Book an Enquiry)
    removeDmcFromSelection: (state, action) => {
      const { dmcId } = action.payload;
      console.log('➖ Redux: Removing DMC from selection - dmcId:', dmcId);
      
      const index = state.selectedDmcIds.indexOf(dmcId);
      if (index > -1) {
        state.selectedDmcIds.splice(index, 1);
        state.selectedDmcsData.splice(index, 1);
        
        console.log('➖ Redux: DMC removed from selection - current selectedDmcIds:', state.selectedDmcIds);
      } else {
        console.log('⚠️ Redux: DMC not found in selection:', dmcId);
      }
    },

    // Clear all selected DMCs (multiple selection - for Book an Enquiry)
    clearSelectedDmcs: (state) => {
      console.log('🗑️ Redux: Clearing all DMC selections (multiple)');
      
      state.selectedDmcIds = [];
      state.selectedDmcsData = [];
      
      console.log('🗑️ Redux: All DMC selections cleared');
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
      })
      // Fetch DMC count - pending
      .addCase(fetchDMCCount.pending, (state) => {
        state.dmcCountLoading = true;
        state.dmcCountError = null;
      })
      // Fetch DMC count - fulfilled
      .addCase(fetchDMCCount.fulfilled, (state, action) => {
        state.dmcCountLoading = false;
        state.dmcCount = action.payload;
        state.dmcCountError = null;
        
        // If dmc_count is 1, automatically store the dmc_id
        if (action.payload && action.payload.dmc_count === 1 && action.payload.dmc_id) {
          console.log('🏪 Redux: Auto-storing DMC ID from count API:', action.payload.dmc_id);
          state.dmcId = action.payload.dmc_id;
          state.selectedDmcData = {
            id: `dmc-auto-${action.payload.dmc_id}`,
            dmcId: action.payload.dmc_id,
            name: `DMC ${action.payload.dmc_id}`,
            location: 'Auto-selected',
            logo: '',
            rating: 4.5,
            description: 'Automatically selected DMC',
            originalData: { dmcId: action.payload.dmc_id }
          };
          console.log('🏪 Redux: Auto-stored DMC ID:', state.dmcId);
        }
      })
      // Fetch DMC count - rejected
      .addCase(fetchDMCCount.rejected, (state, action) => {
        state.dmcCountLoading = false;
        state.dmcCountError = action.payload;
        state.dmcCount = null;
      });
  },
});

// Export actions
export const {
  setSelectedDmcId,
  clearSelectedDmc,
  setSelectedDmcIds,
  addDmcToSelection,
  removeDmcFromSelection,
  clearSelectedDmcs,
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
// New selectors for multiple DMC selection
export const selectSelectedDmcIds = (state) => state.dmc.selectedDmcIds;
export const selectSelectedDmcsData = (state) => state.dmc.selectedDmcsData;
// DMC Count selectors
export const selectDmcCount = (state) => state.dmc.dmcCount;
export const selectDmcCountLoading = (state) => state.dmc.dmcCountLoading;
export const selectDmcCountError = (state) => state.dmc.dmcCountError;

// Export reducer
export default dmcSlice.reducer; 
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

// Get initial DMC ID from localStorage if available (for Agent users only)
const getInitialDmcId = () => {
  try {
    const userRole = Cookies.get("userRole");
    // Only use localStorage for Agent users
    if (userRole === "Agent") {
      const storedDmcId = localStorage.getItem('selectedDmcId');
      return storedDmcId ? parseInt(storedDmcId) : null;
    }
    return null;
  } catch (error) {
    console.error('Error reading DMC ID from localStorage:', error);
    return null;
  }
};

// Get DMC ID from auth state for non-Agent users
const getDmcIdFromAuth = () => {
  try {
    // Check if we're in a browser environment
    if (typeof window !== 'undefined') {
      const userRole = Cookies.get("userRole");
      const dmcId = Cookies.get("dmcId");
      
      // If user role is not "Agent" and dmcId exists, use it
      if (userRole && userRole !== "Agent" && dmcId) {
        console.log('🎯 DMC Slice: Found DMC ID from auth for non-Agent user:', dmcId);
        return parseInt(dmcId);
      }
    }
    return null;
  } catch (error) {
    console.error('Error reading DMC ID from auth:', error);
    return null;
  }
};

// Get DMC logo from auth state for non-Agent users
const getDmcLogoFromAuth = () => {
  try {
    // Check if we're in a browser environment
    if (typeof window !== 'undefined') {
      const userRole = Cookies.get("userRole");
      const dmcLogo = Cookies.get("dmcLogo");
      
      // If user role is not "Agent" and dmcLogo exists, use it
      if (userRole && userRole !== "Agent" && dmcLogo) {
        console.log('🎨 DMC Slice: Found DMC Logo from auth for non-Agent user:', dmcLogo);
        return dmcLogo;
      }
    }
    return null;
  } catch (error) {
    console.error('Error reading DMC Logo from auth:', error);
    return null;
  }
};

// Get DMC company name from auth state for non-Agent users
const getDmcCompanyNameFromAuth = () => {
  try {
    // Check if we're in a browser environment
    if (typeof window !== 'undefined') {
      const userRole = Cookies.get("userRole");
      const dmcCompanyName = Cookies.get("dmcCompanyName");
      
      // If user role is not "Agent" and dmcCompanyName exists, use it
      if (userRole && userRole !== "Agent" && dmcCompanyName) {
        console.log('🏢 DMC Slice: Found DMC Company Name from auth for non-Agent user:', dmcCompanyName);
        return dmcCompanyName;
      }
    }
    return null;
  } catch (error) {
    console.error('Error reading DMC Company Name from auth:', error);
    return null;
  }
};

// Get initial DMC data from localStorage if available
const getInitialDmcData = () => {
  try {
    const storedDmcData = localStorage.getItem('selectedDmcData');
    return storedDmcData ? JSON.parse(storedDmcData) : null;
  } catch (error) {
    console.error('Error reading DMC data from localStorage:', error);
    return null;
  }
};

// Get initial DMC logo from localStorage if available
const getInitialDmcLogo = () => {
  try {
    const storedDmcLogo = localStorage.getItem('selectedDmcLogo');
    return storedDmcLogo || null;
  } catch (error) {
    console.error('Error reading DMC logo from localStorage:', error);
    return null;
  }
};

// Get initial DMC company name from localStorage if available
const getInitialDmcCompanyName = () => {
  try {
    const storedDmcCompanyName = localStorage.getItem('selectedDmcCompanyName');
    return storedDmcCompanyName || null;
  } catch (error) {
    console.error('Error reading DMC company name from localStorage:', error);
    return null;
  }
};

// Initial state
const initialState = {
  dmcs: [],
  loading: false,
  error: null,
  selectedCountries: [],
  lastFetchedCountries: null,
  dmcId: getDmcIdFromAuth() || getInitialDmcId(), // Selected DMC ID (single selection - for Book Tour)
  selectedDmcData: getInitialDmcData(), // Full selected DMC data (single selection - for Book Tour)
  // New fields for DMC logo and company name
  selectedDmcLogo: getDmcLogoFromAuth() || getInitialDmcLogo(), // Selected DMC logo URL
  selectedDmcCompanyName: getDmcCompanyNameFromAuth() || getInitialDmcCompanyName(), // Selected DMC company name
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
      const dmcData = action.payload.dmcData;
      
      console.log('🏪 Redux: Storing selected DMC ID:', dmcId === null ? 'null (dmcId was 0)' : dmcId);
      console.log('🏪 Redux: Storing DMC Data:', dmcData);
      
      // Extract logo and company name from DMC data
      let logo = null;
      let companyName = null;
      
      if (dmcData && dmcData.originalData) {
        logo = dmcData.originalData.logo || null;
        companyName = dmcData.originalData.company_name || null;
        
        console.log('🎨 Redux: Extracted DMC Logo:', logo);
        console.log('🏢 Redux: Extracted DMC Company Name:', companyName);
        console.log('👤 Redux: DMC User ID (userId):', dmcData.originalData.userId);
        console.log('📧 Redux: DMC Email:', dmcData.originalData.email);
        console.log('📞 Redux: DMC Phone:', dmcData.originalData.phone);
        console.log('🌍 Redux: DMC Country:', dmcData.originalData.country);
      }
      
      state.dmcId = dmcId;
      state.selectedDmcData = dmcData || null;
      state.selectedDmcLogo = logo;
      state.selectedDmcCompanyName = companyName;
      
      // Store in localStorage for persistence
      try {
        if (dmcId !== null) {
          localStorage.setItem('selectedDmcId', dmcId.toString());
          localStorage.setItem('selectedDmcData', JSON.stringify(dmcData || null));
          localStorage.setItem('selectedDmcLogo', logo || '');
          localStorage.setItem('selectedDmcCompanyName', companyName || '');
          console.log('💾 localStorage: Stored DMC ID:', dmcId);
          console.log('💾 localStorage: Stored DMC Logo:', logo);
          console.log('💾 localStorage: Stored DMC Company Name:', companyName);
        } else {
          localStorage.removeItem('selectedDmcId');
          localStorage.removeItem('selectedDmcData');
          localStorage.removeItem('selectedDmcLogo');
          localStorage.removeItem('selectedDmcCompanyName');
          console.log('🗑️ localStorage: Removed DMC data');
        }
      } catch (error) {
        console.error('Error storing DMC data in localStorage:', error);
      }
      
      console.log('🏪 Redux: Updated state - dmcId:', state.dmcId);
      console.log('🎨 Redux: Updated state - selectedDmcLogo:', state.selectedDmcLogo);
      console.log('🏢 Redux: Updated state - selectedDmcCompanyName:', state.selectedDmcCompanyName);
    },

    // Clear selected DMC (single selection - for Book Tour)
    clearSelectedDmc: (state) => {
      console.log('🗑️ Redux: Clearing DMC selection - setting dmcId to null');
      
      state.dmcId = null;
      state.selectedDmcData = null;
      state.selectedDmcLogo = null;
      state.selectedDmcCompanyName = null;
      
      // Remove from localStorage
      try {
        localStorage.removeItem('selectedDmcId');
        localStorage.removeItem('selectedDmcData');
        localStorage.removeItem('selectedDmcLogo');
        localStorage.removeItem('selectedDmcCompanyName');
        console.log('🗑️ localStorage: Removed DMC data');
      } catch (error) {
        console.error('Error removing DMC data from localStorage:', error);
      }
      
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
    
    // Set DMC data from auth login (for non-Agent users)
    setDmcFromAuth: (state, action) => {
      const { dmcId, dmcLogo, dmcCompanyName } = action.payload;
      console.log('🔐 DMC Slice: Setting DMC data from auth login');
      console.log('🔐 DMC Slice: dmcId:', dmcId);
      console.log('🔐 DMC Slice: dmcLogo:', dmcLogo);
      console.log('🔐 DMC Slice: dmcCompanyName:', dmcCompanyName);
      
      state.dmcId = dmcId;
      state.selectedDmcLogo = dmcLogo;
      state.selectedDmcCompanyName = dmcCompanyName;
      
      // Store in localStorage for persistence
      try {
        if (dmcId !== null) {
          localStorage.setItem('selectedDmcId', dmcId.toString());
          localStorage.setItem('selectedDmcLogo', dmcLogo || '');
          localStorage.setItem('selectedDmcCompanyName', dmcCompanyName || '');
          console.log('💾 localStorage: Stored DMC data from auth');
        }
      } catch (error) {
        console.error('Error storing DMC data from auth in localStorage:', error);
      }
      
      console.log('🔐 DMC Slice: Updated state from auth login');
    },

    // Reset DMC state
    resetDMCState: (state) => {
      console.log('🔄 Redux: Resetting DMC state to initial values');
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
        
        // If dmc_count is 1, automatically store the dmc_id, logo, and company name
        if (action.payload && action.payload.dmc_count === 1 && action.payload.dmc_id) {
          console.log('🏪 Redux: Auto-storing DMC data from count API');
          console.log('🏪 Redux: dmc_id:', action.payload.dmc_id);
          console.log('🏪 Redux: dmc_logo:', action.payload.dmc_logo);
          console.log('🏪 Redux: dmc_company_name:', action.payload.dmc_company_name);
          console.log('🏪 Redux: dmc_name:', action.payload.dmc_name);
          
          state.dmcId = action.payload.dmc_id;
          state.selectedDmcLogo = action.payload.dmc_logo || null;
          state.selectedDmcCompanyName = action.payload.dmc_company_name || null;
          state.selectedDmcData = {
            id: `dmc-auto-${action.payload.dmc_id}`,
            dmcId: action.payload.dmc_id,
            name: action.payload.dmc_company_name || action.payload.dmc_name || `DMC ${action.payload.dmc_id}`,
            location: 'Auto-selected',
            logo: action.payload.dmc_logo || '',
            rating: 4.5,
            description: 'Automatically selected DMC',
            originalData: { 
              dmcId: action.payload.dmc_id,
              logo: action.payload.dmc_logo,
              company_name: action.payload.dmc_company_name,
              dmc_name: action.payload.dmc_name
            }
          };
          
          // Store in localStorage for persistence
          try {
            localStorage.setItem('selectedDmcId', action.payload.dmc_id.toString());
            localStorage.setItem('selectedDmcData', JSON.stringify(state.selectedDmcData));
            localStorage.setItem('selectedDmcLogo', action.payload.dmc_logo || '');
            localStorage.setItem('selectedDmcCompanyName', action.payload.dmc_company_name || '');
            console.log('💾 localStorage: Auto-stored DMC data');
            console.log('💾 localStorage: DMC ID:', action.payload.dmc_id);
            console.log('💾 localStorage: DMC Logo:', action.payload.dmc_logo);
            console.log('💾 localStorage: DMC Company Name:', action.payload.dmc_company_name);
          } catch (error) {
            console.error('Error storing auto-selected DMC data in localStorage:', error);
          }
          
          console.log('🏪 Redux: Auto-stored DMC data in state');
          console.log('🏪 Redux: dmcId:', state.dmcId);
          console.log('🎨 Redux: selectedDmcLogo:', state.selectedDmcLogo);
          console.log('🏢 Redux: selectedDmcCompanyName:', state.selectedDmcCompanyName);
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
  setDmcFromAuth,
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
// New selectors for DMC logo and company name
export const selectSelectedDmcLogo = (state) => state.dmc.selectedDmcLogo;
export const selectSelectedDmcCompanyName = (state) => state.dmc.selectedDmcCompanyName;
// New selectors for multiple DMC selection
export const selectSelectedDmcIds = (state) => state.dmc.selectedDmcIds;
export const selectSelectedDmcsData = (state) => state.dmc.selectedDmcsData;
// DMC Count selectors
export const selectDmcCount = (state) => state.dmc.dmcCount;
export const selectDmcCountLoading = (state) => state.dmc.dmcCountLoading;
export const selectDmcCountError = (state) => state.dmc.dmcCountError;

// Export reducer
export default dmcSlice.reducer; 
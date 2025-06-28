import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL } from "@/services/api";

// Initial state for the tour packages slice
const initialState = {
  packages: [],
  loading: false,
  error: null,
  searchCriteria: {
    country: null,
    city: null,
    checkIn: null,
    checkOut: null,
    guests: {
      adults: 1,
      children: 0,
      infants: 0,
      maleCount: 0,
      femaleCount: 0,
      childrenAges: []
    }
  },
  packageEnquiryId: null,
  selectedPackages: [],
  AllServices: [],
  packageData: null,
};

console.log("%c REDUX: Initial AllServices state created", "background: #0a3d62; color: #ffffff; padding: 4px; font-weight: bold;", initialState.AllServices);

// Async thunk for fetching tour packages
export const fetchTourPackages = createAsyncThunk(
  "tourPackages/fetchTourPackages",
  async (searchParams, { rejectWithValue, getState }) => {
    const state = getState();
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = state.editing?.agentId;
      console.log("AgentIdpackage", AgentId);

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Extract search parameters
      const { country, city, checkIn, checkOut, guests } = searchParams;

      // Make API request to get tour packages
      const response = await axios.post(
        `${BASE_URL}/create-tour`, 
        {
          destination: country || city,
          check_in: checkIn,
          check_out: checkOut,
          adult: guests.adults,
          child: guests.children,
          infant: guests.infants,
          male: guests.maleCount || 0,
          female: guests.femaleCount || 0,
          children_ages: guests.childrenAges ? guests.childrenAges.join(", ") : ""
        },
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            "agent-id": AgentId,
          },
        }
      );

      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Async thunk for creating a tour package enquiry
export const BookPackageEnquiry = createAsyncThunk(
  "tourPackages/BookPackageEnquiry",
  async (_, { rejectWithValue, getState }) => {
    const state = getState();
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = state.editing?.agentId;
      console.log("AgentIdpackage", AgentId);
      const bookingData = state.tourPackages.AllServices;

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Make API request to create tour package enquiry
      const response = await axios.post(
        `${BASE_URL}/store/custom-booking`,
        bookingData,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            "agent-id": AgentId,
          },
        }
      );

      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const UpdateCustomPackage = createAsyncThunk(
  "tourPackages/UpdateCustomPackage",
  async ({ tour_id }, { rejectWithValue, getState }) => {
    const state = getState();
    try {
      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;
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


      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const response = await axios.get(
        `${BASE_URL}/edit-custom-package?tour_id=${tour_id}`,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            "agent-id": AgentId,
          },
        }
      );

      return response.data;
    } catch (error) { 
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Create the tour packages slice
const tourPackageSlice = createSlice({
  name: "tourPackages",
  initialState,
  reducers: {

    // Add all services
    setAllServices: (state, action) => {
      // Check if action.payload is an array
      if (Array.isArray(action.payload)) {
        console.log("%c REDUX: Setting ALL services in tourPackageSlice", "background: #0a3d62; color: #ffffff; padding: 4px; font-weight: bold;");
        console.log("Previous services count:", state.AllServices.length);
        console.log("Previous services:", [...state.AllServices]);
        console.log("New services count:", action.payload.length);
        console.log("New services:", action.payload);
        
        // Replace the entire state with the new array, filtering out any CustomerInfo type
        state.AllServices = action.payload.filter(service => service.type !== 'CustomerInfo');
        console.log("%c AllServices array replaced", "background: #2ecc71; color: #ffffff; padding: 2px; font-weight: bold;");
      } else {
        console.log("%c REDUX: Adding a service in tourPackageSlice", "background: #0a3d62; color: #ffffff; padding: 4px; font-weight: bold;");
        console.log("Current services:", [...state.AllServices]);
        console.log("Service to add:", action.payload);
        
        // If it's a CustomerInfo type, we don't add it as a separate entry anymore
        if (action.payload.type === 'CustomerInfo') {
          console.log("%c CustomerInfo is now embedded in service data, not added as separate entry", "background: #e74c3c; color: #ffffff; padding: 2px; font-weight: bold;");
          return;
        }
        
        // If it's a single item, add it to the array if it doesn't exist already
        const exists = state.AllServices.some(service => 
          service.id === action.payload.id && service.type === action.payload.type
        );
        
        if (!exists) {
          state.AllServices.push(action.payload);
          console.log("%c Service added successfully. New count:", "background: #2ecc71; color: #ffffff; padding: 2px; font-weight: bold;", state.AllServices.length);
        } else {
          console.log("%c Service already exists, not added.", "background: #e74c3c; color: #ffffff; padding: 2px; font-weight: bold;");
        }
      }
    },

    // Clear all services
    clearAllServices: (state) => {
      console.log("%c REDUX: Clearing ALL services in tourPackageSlice", "background: #c0392b; color: #ffffff; padding: 4px; font-weight: bold;", "Previous count:", state.AllServices.length);
      state.AllServices = [];
      console.log("Services cleared. New count:", state.AllServices.length);
    },

    // Update search criteria
    setSearchCriteria: (state, action) => {
      state.searchCriteria = {
        ...state.searchCriteria,
        ...action.payload,
      };
    },
    
    // Clear search results
    clearPackages: (state) => {
      state.packages = [];
      state.error = null;
    },
    
    // Add/remove selected package
    togglePackageSelection: (state, action) => {
      const packageId = action.payload;
      const existingIndex = state.selectedPackages.indexOf(packageId);
      
      if (existingIndex >= 0) {
        // Remove if already selected
        state.selectedPackages.splice(existingIndex, 1);
      } else {
        // Add to selection
        state.selectedPackages.push(packageId);
      }
    },
    
    // Clear all selected packages
    clearSelectedPackages: (state) => {
      state.selectedPackages = [];
    },
    
    // Reset the enquiry ID
    resetPackageEnquiryId: (state) => {
      state.packageEnquiryId = null;
    },
  },
  extraReducers: (builder) => {
    builder
      // Handle fetchTourPackages states
      .addCase(fetchTourPackages.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTourPackages.fulfilled, (state, action) => {
        state.loading = false;
        state.packages = action.payload.data || [];
      })
      .addCase(fetchTourPackages.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      
      // Handle createPackageEnquiry states
      .addCase(BookPackageEnquiry.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(BookPackageEnquiry.fulfilled, (state, action) => {
        state.loading = false;
        // Store the enquiry ID from the response
        state.packageEnquiryId = action.payload.data?.enquiry_id || action.payload.enquiry_id;
      })
      .addCase(BookPackageEnquiry.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      
      // Handle UpdateCustomPackage states
      .addCase(UpdateCustomPackage.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(UpdateCustomPackage.fulfilled, (state, action) => {
        state.loading = false;
        state.packageData = action.payload;
      })
      .addCase(UpdateCustomPackage.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
        state.packageData = null;
      });
  },
});

// Export actions and reducer
export const { 
  setSearchCriteria, 
  clearPackages, 
  togglePackageSelection,
  clearSelectedPackages,
  resetPackageEnquiryId,
  setAllServices,
  clearAllServices,
} = tourPackageSlice.actions;

// Export selectors
export const selectAttractionBookings = (state) => state.tourPackages.AllServices.filter(service => service.type === 'attraction');
export const selectGuideBookings = (state) => state.tourPackages.AllServices.filter(service => service.type === 'guide');
export const selectRestaurantBookings = (state) => state.tourPackages.AllServices.filter(service => service.type === 'restaurant');

export default tourPackageSlice.reducer; 
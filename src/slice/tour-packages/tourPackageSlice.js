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
  attractionBookings: null,
  guideBookings: null,
  restaurantBookings: null,
  AllServices: [],
};

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
export const createPackageEnquiry = createAsyncThunk(
  "tourPackages/createPackageEnquiry",
  async (enquiryData, { rejectWithValue, getState }) => {
    const state = getState();
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = state.editing?.agentId;
      console.log("AgentIdpackage", AgentId);

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Make API request to create tour package enquiry
      const response = await axios.post(
        `${BASE_URL}/create-package-enquiry`,
        enquiryData,
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
        console.log("New services count:", action.payload.length);
        console.log("New services:", action.payload);
        
        // Replace the entire state with the new array
        state.AllServices = action.payload;
      } else {
        console.log("%c REDUX: Adding a service in tourPackageSlice", "background: #0a3d62; color: #ffffff; padding: 4px; font-weight: bold;");
        console.log("Service to add:", action.payload);
        
        // If it's a single item, add it to the array if it doesn't exist already
        const exists = state.AllServices.some(service => 
          service.id === action.payload.id && service.type === action.payload.type
        );
        
        if (!exists) {
          state.AllServices.push(action.payload);
          console.log("Service added. New count:", state.AllServices.length);
        } else {
          console.log("Service already exists, not added.");
        }
      }
    },

    // Clear all services
    clearAllServices: (state) => {
      state.AllServices = [];
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
    
    // Add attraction bookings
    addAttractionBookings: (state, action) => {
      state.attractionBookings = action.payload;
      console.log("Attraction bookings added to tourPackage slice:", action.payload);
    },
    
    // Clear attraction bookings
    clearAttractionBookings: (state) => {
      state.attractionBookings = null;
    },

    // Add guide bookings
    addGuideBookings: (state, action) => {
      state.guideBookings = action.payload;
      console.log("Guide bookings added to tourPackage slice:", action.payload);
    },
    
    // Clear guide bookings
    clearGuideBookings: (state) => {
      state.guideBookings = null;
    },

    // Add restaurant bookings
    addRestaurantBookings: (state, action) => {
      state.restaurantBookings = action.payload;
      console.log("Restaurant bookings added to tourPackage slice:", action.payload);
    },
    
    // Clear restaurant bookings
    clearRestaurantBookings: (state) => {
      state.restaurantBookings = null;
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
      .addCase(createPackageEnquiry.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(createPackageEnquiry.fulfilled, (state, action) => {
        state.loading = false;
        // Store the enquiry ID from the response
        state.packageEnquiryId = action.payload.data?.enquiry_id || action.payload.enquiry_id;
      })
      .addCase(createPackageEnquiry.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
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
  addAttractionBookings,
  clearAttractionBookings,
  addGuideBookings,
  clearGuideBookings,
  addRestaurantBookings,
  clearRestaurantBookings,
  setAllServices,
  clearAllServices,
} = tourPackageSlice.actions;

// Export selectors
export const selectAttractionBookings = (state) => state.tourPackages.attractionBookings;
export const selectGuideBookings = (state) => state.tourPackages.guideBookings;
export const selectRestaurantBookings = (state) => state.tourPackages.restaurantBookings;

export default tourPackageSlice.reducer; 
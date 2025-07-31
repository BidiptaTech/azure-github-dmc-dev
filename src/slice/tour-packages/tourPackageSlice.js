import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL } from "@/services/api";

// Initial state for the tour packages slice
const initialState = {  
  initialPackages: [],
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
  customerInfoValid: false, // Track if customer info form is valid
};

console.log("%c REDUX: Initial AllServices state created", "background: #0a3d62; color: #ffffff; padding: 4px; font-weight: bold;", initialState.AllServices);

// Async thunk for fetching tour packages
export const fetchTourPackages = createAsyncThunk(
  "tourPackages/fetchTourPackages",
  async (searchParams, { rejectWithValue, getState }) => {
    const state = getState();
    const dmcState = state.dmc;
    const dmcId = dmcState?.dmcId;
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = state.editing?.agentId;
      console.log("AgentIdpackage", AgentId);

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Extract search parameters
      const { country, city, checkIn, checkOut, guests, enq_id } = searchParams;

      // Make API request to get tour packages
      const response = await axios.post(
        `${BASE_URL}/create-tour`, 
        {
          destination: country,
          city: city,
          check_in: checkIn,
          check_out: checkOut,
          adult: guests.adults,
          child: guests.children,
          infant: guests.infants,
          male: guests.maleCount || 0,
          female: guests.femaleCount || 0,
          children_ages: guests.childrenAges ? guests.childrenAges.join(", ") : "",
          enquiry_id: enq_id || null,
          dmc_id: dmcId || null
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
export const UpdateCustomBooking = createAsyncThunk(
  "tourPackages/UpdateCustomBooking",
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

      const response = await axios.post(
        `${BASE_URL}/update-custom-package`,
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

// Create the tour packages slice
const tourPackageSlice = createSlice({
  name: "tourPackages",
  initialState,
  reducers: {

    // Add all services
    setAllServices: (state, action) => {
      // 🔹 If payload is an array (bulk)
      if (Array.isArray(action.payload)) {
        console.log("%c REDUX: Setting ALL services in tourPackageSlice", "background: #0a3d62; color: #ffffff; padding: 4px; font-weight: bold;");
        console.log("Previous services count:", state.AllServices.length);
        console.log("Previous services:", [...state.AllServices]);
        console.log("New services count:", action.payload.length);
        console.log("New services:", action.payload);
    
        // Preserve search form data from existing services
        const preservedSearchFormData = [...state.AllServices].filter(service =>
          'tour_id' in service &&
          'country' in service &&
          'city' in service &&
          'check_in_time' in service &&
          'check_out_time' in service &&
          !service.type // optional: only preserve if no type
        );

        // Preserve existing services with id and type (that won't be updated)
        const preservedExistingServices = [...state.AllServices].filter(service =>
          'id' in service && 'type' in service
        );
    
        const newServices = [];
        const updatedServices = [];
    
        action.payload.forEach(newService => {
          // Skip CustomerInfo
          if (newService.type === 'CustomerInfo') {
            console.log("%c Skipping CustomerInfo in array", "background: #e74c3c; color: #ffffff; padding: 2px;");
            return;
          }
    
          // Search form data — always add if not already present
          if (
            'tour_id' in newService &&
            'country' in newService &&
            'city' in newService &&
            'check_in_time' in newService &&
            'check_out_time' in newService &&
            !newService.type 
          ) {
            const exists = preservedSearchFormData.some(service =>
              service.tour_id === newService.tour_id &&
              service.country === newService.country &&
              service.city === newService.city &&
              service.check_in_time === newService.check_in_time &&
              service.check_out_time === newService.check_out_time
            );
    
            if (!exists) {
              preservedSearchFormData.push(newService);
              console.log("%c Added unique search form data", "background: #8e44ad; color: #ffffff; padding: 2px;");
            } else {
              console.log("%c Duplicate search form data, skipped", "background: #e67e22; color: #ffffff; padding: 2px;");
            }
    
            return; // don't process further as normal service
          }
    
          // Normal service with id and type
          if ('id' in newService && 'type' in newService) {
            const existingServiceIndex = preservedExistingServices.findIndex(service =>
              service.id === newService.id && service.type === newService.type
            );
    
            if (existingServiceIndex >= 0) {
              // Update existing service
              preservedExistingServices[existingServiceIndex] = newService;
              console.log("%c Updated existing service with id/type", "background: #3498db; color: #ffffff; padding: 2px;");
            } else {
              // Add new service
              newServices.push(newService);
              console.log("%c Standard service added from array", "background: #27ae60; color: #ffffff; padding: 2px;");
            }
          } else {
            // Service without id/type, just add it
            newServices.push(newService);
            console.log("%c Service without id/type added", "background: #9b59b6; color: #ffffff; padding: 2px;");
          }
        });
    
        // Set new state: preserved search form data + preserved/updated existing services + new services
        state.AllServices = [...preservedSearchFormData, ...preservedExistingServices, ...newServices];
        console.log("%c Final AllServices state updated (array)", "background: #2ecc71; color: #ffffff; padding: 2px;");
        console.log("Final services count:", state.AllServices.length);
      }
    
      // 🔹 If payload is a single object
      else {
        const newService = action.payload;
        console.log("%c REDUX: Adding a service in tourPackageSlice", "background: #0a3d62; color: #ffffff; padding: 4px; font-weight: bold;");
        console.log("Current services:", [...state.AllServices]);
        console.log("Service to add:", newService);
    
        if (newService.type === 'CustomerInfo') {
          console.log("%c CustomerInfo is embedded, not stored", "background: #e74c3c; color: #ffffff; padding: 2px;");
          return;
        }
    
        let exists = false;
    
        // Check for standard service
        if ('id' in newService && 'type' in newService) {
          exists = state.AllServices.some(service =>
            service.id === newService.id && service.type === newService.type
          );
        }
        // Check for search form data
        else if (
          'tour_id' in newService &&
          'country' in newService &&
          'city' in newService &&
          'check_in_time' in newService &&
          'check_out_time' in newService
        ) {
          exists = state.AllServices.some(service =>
            service.tour_id === newService.tour_id &&
            service.country === newService.country &&
            service.city === newService.city &&
            service.check_in_time === newService.check_in_time &&
            service.check_out_time === newService.check_out_time
          );
        }
    
        if (!exists) {
          state.AllServices.push(newService);
          console.log("%c Service added successfully", "background: #27ae60; color: #ffffff; padding: 2px;");
        } else {
          console.log("%c Service already exists, not added.", "background: #e74c3c; color: #ffffff; padding: 2px; font-weight: bold;");
        }
      }
    },

    setPackageData: (state, action) => {
      state.packageData = action.payload;
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
      state.initialPackages = [];
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

    // Set customer info validity
    setCustomerInfoValid: (state, action) => {
      state.customerInfoValid = action.payload;
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
        state.initialPackages = action.payload.data || [];
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
      })
      
      .addCase(UpdateCustomBooking.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(UpdateCustomBooking.fulfilled, (state, action) => {
        state.loading = false;
        state.packageData = action.payload;
      })
      .addCase(UpdateCustomBooking.rejected, (state, action) => {
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
  setPackageData,
  setCustomerInfoValid,
} = tourPackageSlice.actions;

// Export selectors
export const selectAttractionBookings = (state) => state.tourPackages.AllServices.filter(service => service.type === 'attraction');
export const selectGuideBookings = (state) => state.tourPackages.AllServices.filter(service => service.type === 'guide');
export const selectRestaurantBookings = (state) => state.tourPackages.AllServices.filter(service => service.type === 'restaurant');

export default tourPackageSlice.reducer; 
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
  tourStatus: null,
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
        userRole === "Assistant Manager (DMC)" ||
        userRole === "Operational Head(DMC)" ||
        userRole === "DMC Operational Manager" ||
        userRole === "DMC Assistant Operational Manager"
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
    console.log("Previous services by booking_id:", state.AllServices.filter(s => s.booking_id).map(s => ({
      booking_id: s.booking_id,
      type: s.type,
      tour_id: s.tour_id
    })));
    console.log("New services count:", action.payload.length);
    console.log("New services:", action.payload);
    console.log("New services by booking_id:", action.payload.filter(s => s.booking_id).map(s => ({
      booking_id: s.booking_id,
      type: s.type,
      tour_id: s.tour_id
    })));

    const currentServicesByType = state.AllServices.reduce((acc, service) => {
      // Skip search form data in type counting
      if ('tour_id' in service && 'country' in service && 'city' in service && 
          'check_in_time' in service && 'check_out_time' in service && !service.type) {
        return acc;
      }
      const type = service.type || 'unknown';
      acc[type] = (acc[type] || 0) + 1;
      return acc;
    }, {});

    // Check if this is a removal operation (fewer services than before)
    const isRemovalOperation = action.payload.length < Object.values(currentServicesByType).reduce((sum, count) => sum + count, 0);
    console.log("%c Is removal operation:", isRemovalOperation, "background: #e67e22; color: #ffffff; padding: 2px;");

    if (isRemovalOperation) {
      console.log("%c Removal operation detected - analyzing removal context", "background: #e74c3c; color: #ffffff; padding: 2px;");

      // Get service types and counts
      const currentServicesByType = state.AllServices.reduce((acc, service) => {
        // Skip search form data in type counting
        if ('tour_id' in service && 'country' in service && 'city' in service && 
            'check_in_time' in service && 'check_out_time' in service && !service.type) {
          return acc;
        }
        const type = service.type || 'unknown';
        acc[type] = (acc[type] || 0) + 1;
        return acc;
      }, {});

      
      const newServicesByType = action.payload.reduce((acc, service) => {
        // Skip search form data in type counting
        if ('tour_id' in service && 'country' in service && 'city' in service && 
            'check_in_time' in service && 'check_out_time' in service && !service.type) {
          return acc;
        }
        const type = service.type || 'unknown';
        acc[type] = (acc[type] || 0) + 1;
        return acc;
      }, {});

      
      console.log("Current services by type:", currentServicesByType);
      console.log("New services by type:", newServicesByType);
      
      // Find missing types and their original counts
      const currentTypes = Object.keys(currentServicesByType);
      const newTypes = Object.keys(newServicesByType);
      const missingTypes = currentTypes.filter(type => !newTypes.includes(type));
      
      console.log("Missing service types:", missingTypes);
      
      // Calculate removal context metrics
      const totalCurrentServices = Object.values(currentServicesByType).reduce((sum, count) => sum + count, 0);
      const totalNewServices = Object.values(newServicesByType).reduce((sum, count) => sum + count, 0);
      const removalPercentage = totalCurrentServices > 0 ? ((totalCurrentServices - totalNewServices) / totalCurrentServices) * 100 : 0;

      
      console.log(`Removing ${totalCurrentServices - totalNewServices} services (${removalPercentage.toFixed(1)}% of total)`);
      
      // Determine if this is likely accidental removal
      // It's likely accidental if:
      // 1. Multiple entire service types are missing (bulk accidental removal)
      // 2. OR large percentage of services are being removed (>50%) while keeping diverse types
      // 3. OR the payload contains multiple different service types (suggesting it's not a targeted removal)
      
      const isLikelyAccidental = (
        // Multiple types completely missing suggests accidental bulk removal
        missingTypes.length > 1 ||
        // High removal percentage with diverse remaining types suggests accidental removal
        (removalPercentage > 50 && newTypes.length > 1) ||
        // If removing small number of services but they represent entire types, it might be accidental
        // UNLESS it's just one service of one type being removed (intentional)
        (missingTypes.length === 1 && 
         currentServicesByType[missingTypes[0]] > 1 && 
         newTypes.length > 1)
      );
      
      // Special case: if removing all services except search forms, it's likely intentional clear operation
      const onlySearchFormsRemain = action.payload.every(service => 
        'tour_id' in service && 'country' in service && 'city' in service && 
        'check_in_time' in service && 'check_out_time' in service && !service.type
      );
      
      console.log("Is likely accidental removal:", isLikelyAccidental);
      console.log("Only search forms remain:", onlySearchFormsRemain);
      
      if (isLikelyAccidental && !onlySearchFormsRemain) {
        console.log("%c Likely accidental removal - preserving missing service types", "background: #f39c12; color: #ffffff; padding: 2px;");
        
        const preservedServices = state.AllServices.filter(service => {
          // Always preserve search form data
          if ('tour_id' in service && 'country' in service && 'city' in service && 
              'check_in_time' in service && 'check_out_time' in service && !service.type) {
            return true;
          }
          
          // Preserve services of missing types
          if (service.type && missingTypes.includes(service.type)) {
            console.log(`Preserving ${service.type} service with booking_id: ${service.booking_id} (accidental removal prevention)`);
            return true;
          }
          
          return false;
        });
        
        state.AllServices = [...preservedServices, ...action.payload];
      } else {
        // Likely intentional removal - trust the component's decision
        console.log("%c Likely intentional removal - trusting component's filtered list", "background: #27ae60; color: #ffffff; padding: 2px;");
        state.AllServices = [...action.payload];
      }
      
      console.log('Final AllServices state (removal):', state.AllServices);
      console.log('Final state by type:', state.AllServices.reduce((acc, s) => {
        const key = s.type || (s.country && s.city ? 'search_form' : 'unknown');
        acc[key] = (acc[key] || 0) + 1;
        return acc;
      }, {}));
      return;
    }

    // Preserve search form data
    const preservedSearchFormData = [...state.AllServices].filter(service =>
      'tour_id' in service &&
      'country' in service &&
      'city' in service &&
      'check_in_time' in service &&
      'check_out_time' in service &&
      !service.type
    );

    // Existing services with booking_id (ignore type now)
    const existingServices = [...state.AllServices].filter(service =>
      'booking_id' in service && service.booking_id
    );
    console.log("Existing services:", existingServices);
    console.log("Existing services details:", existingServices.map(s => ({
      booking_id: s.booking_id,
      type: s.type,
      tour_id: s.tour_id
    })));
    
    const finalServices = [];
    const processedBookingIds = new Set();

    action.payload.forEach(newService => {
      if (newService.type === 'CustomerInfo') {
        console.log("%c Skipping CustomerInfo in array", "background: #e74c3c; color: #ffffff; padding: 2px;");
        return;
      }

      // Search form data → preserve separately
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

        return;
      }

      // Normal service with booking_id
      if ('booking_id' in newService && newService.booking_id) {
        console.log(`%c Processing service with booking_id: ${newService.booking_id} (type: ${newService.type})`, "background: #2c3e50; color: #ffffff; padding: 2px;");

        processedBookingIds.add(newService.booking_id);

        const existingServiceIndex = existingServices.findIndex(service =>
          service.booking_id === newService.booking_id
        );

        if (existingServiceIndex >= 0) {
          console.log(`%c UPDATING existing service with booking_id: ${newService.booking_id}`, "background: #e74c3c; color: #ffffff; padding: 2px; font-weight: bold;");
          finalServices.push(newService);
        } else {
          console.log(`%c ADDING new service with booking_id: ${newService.booking_id}`, "background: #27ae60; color: #ffffff; padding: 2px; font-weight: bold;");
          finalServices.push(newService);
        }
      } else {
        // Service without booking_id → always add
        console.log(`%c Adding NEW service without booking_id (type: ${newService.type || 'unknown'})`, "background: #9b59b6; color: #ffffff; padding: 2px;");
        finalServices.push(newService);
      }
    });

    // Preserve unprocessed existing services
    console.log(`%c Checking ${existingServices.length} existing services for preservation`, "background: #f39c12; color: #ffffff; padding: 2px; font-weight: bold;");
    console.log("Processed booking IDs:", Array.from(processedBookingIds));
    
    existingServices.forEach(existingService => {
      if (!processedBookingIds.has(existingService.booking_id)) {
        finalServices.push(existingService);
        console.log(`%c PRESERVED existing service with booking_id: ${existingService.booking_id} (type: ${existingService.type})`, "background: #f39c12; color: #ffffff; padding: 2px; font-weight: bold;");
      } else {
        console.log(`%c SKIPPED existing service with booking_id: ${existingService.booking_id} (already processed)`, "background: #e67e22; color: #ffffff; padding: 2px;");
      }
    });

    // Log what we're about to set
    console.log("%c === FINAL STATE COMPOSITION ===", "background: #2c3e50; color: #ffffff; padding: 4px; font-weight: bold;");
    console.log("Preserved search form data count:", preservedSearchFormData.length);
    console.log("Final services count:", finalServices.length);
    console.log("Final services by type:", finalServices.reduce((acc, s) => {
      acc[s.type || 'unknown'] = (acc[s.type || 'unknown'] || 0) + 1;
      return acc;
    }, {}));
    console.log("Final services booking_ids:", finalServices.map(s => `${s.booking_id} (${s.type})`));
    
    state.AllServices = [...preservedSearchFormData, ...finalServices];
    console.log('Final AllServices state:', state.AllServices);
    console.log('Final AllServices by type:', state.AllServices.reduce((acc, s) => {
      const key = s.type || (s.country && s.city ? 'search_form' : 'unknown');
      acc[key] = (acc[key] || 0) + 1;
      return acc;
    }, {}));
    console.log("%c Final AllServices state updated (array)", "background: #2ecc71; color: #ffffff; padding: 2px;");
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

    // Standard service with booking_id
    if ('booking_id' in newService && newService.booking_id) {
      exists = state.AllServices.some(service =>
        service.booking_id === newService.booking_id
      );
    }
    // Search form data
    else if (
      'tour_id' in newService &&
      'country' in newService &&
      'city' in newService &&
      'check_in_time' in newService &&
      'check_out_time' in newService &&
      !newService.type
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
        state.tourStatus = action.payload?.tour?.tour_status;
        console.log("tourStatus", state.tourStatus);
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
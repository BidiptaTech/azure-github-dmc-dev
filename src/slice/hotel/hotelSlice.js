import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { format } from "date-fns";
import dayjs from "dayjs";
import { useDispatch } from "react-redux";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { BASE_URL } from "@/services/api";
import { useSelector } from "react-redux";
import { updateServiceResponse } from "@/slice/common/stepperButtonSlice";

// Selector to get DMC ID from dmc slice
const selectDmcId = (state) => state.dmc?.dmcId;

// Async thunk for fetching hotels
export const fetchHotels = createAsyncThunk(
  "hotels/fetchHotels",
  async (params = {}, { getState, rejectWithValue }) => {
    const { start, limit } = params;
    try {
      const state = getState();
     const { location, ucheckIn, ucheckOut, guests } = state.hotels.searchState;
     const selectedDmcId = selectDmcId(state);
     console.log('🎯 HotelSlice - Selected DMC ID from Redux:', selectedDmcId);
     
    //  console.log( state.hotels.searchState,"hotel details>>>>>");
     
    //  console.log(ucheckIn,"start date");
    //  console.log(ucheckOut,"endDate");
     
     

     const { adults, children, infant } = guests;

      // Ensure stateSearchLocation is an array and join it into a string
      const formattedLocation = Array.isArray(location)
        ? location.join(",")
        : location;
       const dateRange=[ucheckIn,ucheckOut]
     
   
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const response = await axios.get(
        `${BASE_URL}/location`,
        {
          params: {
            location: formattedLocation,
            date:JSON.stringify(dateRange) , // Added check-out
            start: start ? start : undefined,
            limit: limit ? limit : undefined,
            adults,
            children,
            infant,
            dmc_id: selectedDmcId ? JSON.stringify([selectedDmcId]) : JSON.stringify([]) // Pass DMC ID as JSON string array for hotel listing
                // Added guests
          },
        
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
          },
        }
      );

     console.log("API Response:", response.data);
      return response.data;
    } catch (error) {
     console.error("API Error:", error);
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const hottelBookingDataSubmit = createAsyncThunk(
  "hotels/hotelBookingDataSubmit",
  async (params, { getState, dispatch, rejectWithValue }) => {
    try {
      const state = getState();
      const selectedDmcId = selectDmcId(state);
      console.log('🎯 HotelSlice - Selected DMC ID from Redux:', selectedDmcId);
      
      const authToken = Cookies.get("authToken");
      let { submitHotels, id, type } = state.hotels;
      // Get agent information from state instead of using hooks
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;

      // Handle different parameter formats for backward compatibility
      let bookingType = "booking";
      let enquiryPrice = null;
      let comment = null;

      if (typeof params === "string") {
        // Handle old format where only bookingType was passed as a string
        bookingType = params;
      } else if (params && typeof params === "object") {
        // Handle new format where params is an object with multiple properties
        bookingType = params.bookingType || "booking";
        enquiryPrice = params.enquiryPrice;
        comment = params.comment;
      }

      // Determine AgentId based on user role
      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)" ||
        userRole === "DMC Assistant Operational Manager" ||
        userRole === "DMC Operational Manager" ||
        userRole === "Operational Head(DMC)"
      ) {
        AgentId = agentID;
      } else {
        AgentId = Cookies.get("AgentId");
      }
      // Make a copy of submitHotels to avoid mutating state directly
      let hotelData = Array.isArray(submitHotels)
        ? [...submitHotels]
        : [{ ...submitHotels }];

      // Create the base formData object
      let formData = {
        data: hotelData,
        type: type,
        bookingType: bookingType,
        agent_id: AgentId,
        tour_id: id,
        dmc_id: selectedDmcId, // Add DMC ID to booking request
      };

      // If this is an enquiry, add the enquiry data to the root level of formData
      if (bookingType === "enquiry") {
        if (enquiryPrice !== null && enquiryPrice !== undefined) {
          formData.enquiryPrice = enquiryPrice;
        }

        if (comment) {
          formData.comment = comment;
        }
      }

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      console.log("Booking request data:", formData); // Log the complete request for debugging

      const response = await axios.post(
        `${BASE_URL}/create-booking`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
          },
        }
      );

      console.log("API Response:", response.data);
      
      // Update stepper button visibility based on booking response
      dispatch(updateServiceResponse({ 
        service: 'hotel', 
        response: response.data 
      }));
      
      return response.data;
    } catch (error) {
      console.error("API Error:", error);
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const hotelSlice = createSlice({
  name: "hotels",
  initialState: {
    submitHotels: [], //here i am storing booking data
    hotels: [], // Stores hotel data
    status: "idle", // Tracks the status: 'idle', 'loading', 'succeeded', 'failed'
    hasMore: true, // Indicates if more data is available for pagination
    start: 0,
    error: null, // Stores any errors from API requests
    locations: [], // Stores location data
    id: 0,
    type: "hotel",
    tourdetails: [],
    searchState: {
      location: [], // Search location
      ucheckIn: null, // Check-in date
      ucheckOut: null, // Check-out date
      guests: { adults: 1, children: 0, infant: 0 }, // Default guest count
    },
    hotelService: [], //storeing booking respone
    bookingResponse: null,
    selectedPriceMode: {},
  },

  reducers: {
    updatePaginationState: (state, action) => {
      // Allow direct updates to pagination-related state
      if (action.payload.hasMore !== undefined) {
        state.hasMore = action.payload.hasMore;
      }
      if (action.payload.start !== undefined) {
        state.start = action.payload.start;
      }
      console.log(`Updated pagination state: hasMore=${state.hasMore}, start=${state.start}`);
    },
    setSelectedPriceMode: (state, action) => {
      state.selectedPriceMode = action.payload; // Just store the selected mode directly as a string
    },
    setId: (state, action) => {
      state.id = action.payload;
    },
    setLocations: (state, action) => {
      state.locations = action.payload;
    },
    setHotelBooking: (state, action) => {
      state.submitHotels = action.payload || [];
      // console.log("hotell", state.submitHotels);
    },
    setHotelService: (state, action) => {
      state.hotelService = action.payload || [];
      //console.log("hotelServie Responsee", state.hotelService);
    },
    updateSearchState: (state, action) => {
      const updatedState = { ...action.payload };

      // Handle check-in date
      if (updatedState.ucheckIn) {
        updatedState.ucheckIn = dayjs(
          updatedState.ucheckIn,
          "YYYY-MM-DD"
        ).format("YYYY-MM-DD");
      }

      // Handle check-out date
      if (updatedState.ucheckOut) {
        updatedState.ucheckOut = dayjs(
          updatedState.ucheckOut,
          "YYYY-MM-DD"
        ).format("YYYY-MM-DD");
      }

      state.searchState = {
        ...state.searchState,
        ...updatedState,
      };

      console.log("Updated searchState:", state.searchState);
    },
    settourdetails: (state, action) => {
      state.tourdetails = action.payload;
      console.log("tdetails", state.tourdetails);
    },
    resetHotels: (state) => {
      state.hotels = [];
      state.start = 0;
      //state.searchState.location = [];
      state.hasMore = true;
      state.status = "idle"; // Ensure the status is reset
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchHotels.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchHotels.fulfilled, (state, action) => {
        state.status = "succeeded";
        
        // Check if response is an array (has data) or empty (no more data)
        if (Array.isArray(action.payload) && action.payload.length > 0) {
          // Avoid duplicate hotels
          const existingIds = state.hotels.map((hotel) => hotel.id);
          const newHotels = action.payload.filter(
            (hotel) => !existingIds.includes(hotel.id)
          );

          state.hotels = [...state.hotels, ...newHotels];
          
          // Only set hasMore to true if we received the full amount requested
          // This prevents additional calls if we receive fewer items than requested
          state.hasMore = newHotels.length === 5; // Assuming limit is 5
          
          // Increment the start value
          if (newHotels.length > 0) {
            state.start += newHotels.length;
          }
        } else {
          // No more data available, stop pagination
          state.hasMore = false;
        }
        
        console.log(`Hotels after update: ${state.hotels.length}, hasMore: ${state.hasMore}`);
      })
      .addCase(fetchHotels.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || action.error.message;
        
        // If we get a 404 error, it means there are no more hotels to fetch
        if (action.error && (action.error.message.includes('404') || 
            (action.payload && action.payload.status === 404))) {
          state.hasMore = false;
          console.log("No more hotels available, stopping pagination");
        }
      })
      .addCase(hottelBookingDataSubmit.pending, (state) => {
        state.status = "loading";
      })
      .addCase(hottelBookingDataSubmit.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.bookingResponse = action.payload;
        // console.log("Hotel booking success:", action.payload);
      })
      .addCase(hottelBookingDataSubmit.rejected, (state, action) => {
        state.status = "failed";
        // console.error("Hotel booking failed:", action.payload);
      });
  },
});

export const {
  updatePaginationState,
  setSelectedPriceMode,
  selectedPriceMode,
  setLocations,
  updateSearchState,
  resetHotels,
  setId,
  settourdetails,
  setHotelBooking,
  setHotelService,
} = hotelSlice.actions;

export default hotelSlice.reducer;

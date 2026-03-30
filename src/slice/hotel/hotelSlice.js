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
import { setHaveBooking } from "@/slice/common/commonSlice";
import { setTourId, updateStepStatus, statusUpdate, setType } from "@/slice/common/stepsSlice";
import { setTourIdd } from "@/slice/common/authSlices";
// Note: We no longer ensure/create tour here; tour is created during booking

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
    //  console.log('🎯 HotelSlice - Selected DMC ID from Redux:', selectedDmcId);
     
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

    //  console.log("API Response:", response.data);
      return response.data;
    } catch (error) {
     // console.error("API Error:", error);
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
      // console.log('🎯 HotelSlice - Selected DMC ID from Redux:', selectedDmcId);
      
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

      // Check if we have an existing tour_id from hotel state or global auth state
      const globalTourId = getState().auth?.tourId || getState().steps?.id;
      const effectiveTourId = id || globalTourId;
      
      // Extract numeric part from tour_id (e.g., "DMC-ORD2904" -> "2904")
      let numericTourId = "";
      if (effectiveTourId) {
        const tourIdStr = String(effectiveTourId);
        const match = tourIdStr.match(/\d+$/); // Extract trailing digits
        numericTourId = match ? match[0] : tourIdStr;
      }
      
      const hasTourId = numericTourId && Number(numericTourId) > 0;

      // Create the base formData object
      let formData = {
        data: hotelData,
        type: type,
        bookingType: bookingType,
        agent_id: AgentId,
        tour_id: hasTourId ? Number(numericTourId) : "",
        dmc_id: selectedDmcId,
      };

      // Get root state for destination lookup
      const root = getState();
      const bookings = root.bookings || {};
      const auth = root.auth || {};

      // Create dynamic country mapping from auth state
      const countryCodeToName = {};
      if (auth.user_country && Array.isArray(auth.user_country)) {
        auth.user_country.forEach((country) => {
          if (country && country.name && country.code) {
            countryCodeToName[country.code] = country.name;
            countryCodeToName[country.code.toLowerCase()] = country.name;
          }
        });
      }

      // Determine destination - check multiple sources
      let destination = "";
      
      // Priority 1: Check if destination is in the payload (from index2.jsx)
      if (hotelData && hotelData.length > 0 && hotelData[0]?.destination) {
        destination = hotelData[0].destination;
        // Remove destination from data array to avoid duplication
        hotelData = hotelData.map(item => {
          const { destination: _, ...rest } = item;
          return rest;
        });
        formData.data = hotelData;
      }
      // Priority 2: Check tourDetails
      else if (root.hotels?.tourdetails?.destination) {
        const tourDest = root.hotels.tourdetails.destination;
        destination = Array.isArray(tourDest) ? tourDest.join(", ") : tourDest;
      }
      // Priority 3: Check enquiry state
      else if (root.enquiry?.destination) {
        const enquiryDest = root.enquiry.destination;
        destination = Array.isArray(enquiryDest) ? enquiryDest.join(", ") : enquiryDest;
      }
      // Priority 4: Use bookings.searchLocation (convert codes to names)
      else if (bookings.searchLocation) {
        const searchLocation = bookings.searchLocation || [];
        destination = (Array.isArray(searchLocation) ? searchLocation : [searchLocation])
          .map((loc) => countryCodeToName[loc] || loc)
          .join(", ");
      }
      // Priority 5: Check searchState location
      else if (root.hotels?.searchState?.location) {
        const searchStateLoc = root.hotels.searchState.location;
        destination = Array.isArray(searchStateLoc) ? searchStateLoc.join(", ") : searchStateLoc;
      }

      // Add destination at root level if we found one
      if (destination) {
        formData.destination = destination;
      }

      // Only include other tour meta if we don't have a tour_id yet
      if (!hasTourId) {
        const check_in = bookings.checkIn || "";
        const check_out = bookings.checkOut || "";
        const bGuests = bookings.guests || {};
        const adult = bGuests.adults ?? 1;
        const child = bGuests.children ?? 0;
        const infant = bGuests.infant ?? 0;
        const male = bGuests.maleCount ?? 0;
        const female = bGuests.femaleCount ?? 0;
        const children_ages = (bGuests.childrenAges || []).join(", ");

        // Add tour meta to formData
        formData.check_in = check_in;
        formData.check_out = check_out;
        formData.adult = adult;
        formData.child = child;
        formData.infant = infant;
        formData.male = male;
        formData.female = female;
        formData.children_ages = children_ages;
      }

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

      // console.log("Booking request data:", formData); // Log the complete request for debugging

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

      // console.log("API Response:", response.data);
      
      // Extract and dispatch tour_id if this was the first booking (tour created)
      const tourId = response.data?.order?.tour_id || response.data?.tour_id;
      if (tourId) {
        dispatch(setId(tourId));
        dispatch(setTourId(tourId));
        dispatch(setTourIdd(tourId));
        console.log("Tour ID created and stored:", tourId);
        
        // Update step status to mark hotel as completed
        dispatch(updateStepStatus({ key: 'hotel', status: 3 }));
        dispatch(setType(null));
        dispatch(statusUpdate());
      }
      
      // Update stepper button visibility based on booking response
      dispatch(updateServiceResponse({ 
        service: 'hotel', 
        response: response.data 
      }));
      
      return response.data;
    } catch (error) {
      // console.error("API Error:", error);
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
      // console.log(`Updated pagination state: hasMore=${state.hasMore}, start=${state.start}`);
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

      // console.log("Updated searchState:", state.searchState);
    },
    settourdetails: (state, action) => {
      state.tourdetails = action.payload;
      // console.log("tdetails", state.tourdetails);
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
        // console.log(`Hotels after update: ${state.hotels.length}, hasMore: ${state.hasMore}`);
      })
      .addCase(fetchHotels.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || action.error.message;
        
        // If we get a 404 error, it means there are no more hotels to fetch
        if (action.error && (action.error.message.includes('404') || 
            (action.payload && action.payload.status === 404))) {
          state.hasMore = false;
          // console.log("No more hotels available, stopping pagination");
        }
      })
      .addCase(hottelBookingDataSubmit.pending, (state) => {
        state.status = "loading";
      })
      .addCase(hottelBookingDataSubmit.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.bookingResponse = action.payload;
        
        // Extract and store tour_id from booking response
        const tourId = action.payload?.order?.tour_id || action.payload?.tour_id;
        if (tourId) {
          state.id = tourId;
          // console.log("Tour ID captured from booking response:", tourId);
        }
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

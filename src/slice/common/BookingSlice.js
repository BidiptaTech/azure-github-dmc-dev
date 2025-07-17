import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { setTourIdd } from "./authSlices";
import { setDateService } from "./dateServicesSlice";
import { setAttractionService } from "../attractions/attractionSlice";
import { setRestaurantsService } from "../restaurant/RestaurantsSlice";
import { setEntryport, setExitport } from "../port/pickupDropSlice";
import { setHourly, setPointToPoint } from "../localtour/Localslice";
// import { setbookedGuide } from "../tourguide/guideslice";
import { BASE_URL } from "@/services/api";
import { setCity } from "./citySlice";

export const fetchBookingid = createAsyncThunk(
  "fetchBookingid",
  async (_, { getState, rejectWithValue, dispatch }) => {
    const state = getState().bookings; // Access the bookings slice
    const authState = getState().auth; // Access the auth slice

    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken && !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const { searchLocation, checkIn, checkOut, guests } = state;
      const { maleCount, femaleCount } = guests;

      // Create dynamic country mapping from auth state
      const countryCodeToName = {};
      if (authState.user_country && Array.isArray(authState.user_country)) {
        authState.user_country.forEach(country => {
          if (country && country.name && country.code) {
            countryCodeToName[country.code] = country.name;
            countryCodeToName[country.code.toLowerCase()] = country.name;
          }
        });
      }

      // Prepare request body
      const requestBody = {
        destination: searchLocation
          .map((location) => countryCodeToName[location] || location)
          .join(", "),
        check_in: checkIn,
        check_out: checkOut,
        adult: guests.adults,
        child: guests.children,
        infant: guests.infant,
        male: maleCount,
        female: femaleCount,
        children_ages: guests.childrenAges.join(", "),
        enquiry_id: null
      };

      console.log("Request Body:", requestBody);

      const response = await axios.post(
        `${BASE_URL}/create-tour`,
        requestBody,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            "agent-id": AgentId,
          },
        }
      );
      // console.log("Tour create", response);

      const { tour_id, data } = response.data;

      // Dispatch setTourIdd if needed
      if (tour_id) {
        dispatch(setTourIdd(data.tour_id));
      }
      if (data?.service?.date_service) {
        // console.log("Date Service Data:", data.service?.date_service);
        dispatch(setDateService(data.service?.date_service));
      }

      if (data?.city) {
        // console.log("City Data:", data.city);
        dispatch(setCity(data.city));
        // console.log("City Data:", data.city);
        dispatch(setCity(data.city));
      }
      if (data) {
        // console.log("Service Data:", data);
        //dispatch(setDateService(data.service));
      }

      // Dispatch the date_service data from nested service object to Redux
      if (data?.service) {
        // console.log("Service Data:", data.service);
        //dispatch(setDateService(data.service));
      }
      // Dispatch the date_service data from nested service object to Redux
      if (data?.service?.attraction || data?.service?.attraction_package) {
        // Combine both attraction and attraction_package data
        const attractionData = data.service.attraction || [];
        const attractionPackageData = data.service.attraction_package || [];
        const combinedAttractionData = [...attractionData, ...attractionPackageData];
        
        // console.log("Combined Attraction Data:", combinedAttractionData);
        dispatch(setAttractionService(combinedAttractionData));
      }

      if (data?.service?.restaurant) {
        // console.log("Service Data:", data?.service?.restaurant);
        dispatch(setRestaurantsService(data.service.restaurant));
      }
      if (data?.service?.entry_port) {
        // console.log("entry port Data:", data?.service?.entry_port);
        dispatch(setEntryport(data.service.entry_port));
      }
      if (data?.service?.exit_port) {
        // console.log("exit port Data:", data?.service?.exit_port);
        dispatch(setExitport(data.service.exit_port));
      }
      if (data?.service?.travel_point) {
        // console.log("pointtopoint Data:", data?.service?.travel_point);
        dispatch(setPointToPoint(data.service.travel_point));
      }
      if (data?.service?.travel_hourly) {
        // console.log("hourly Data:", data?.service?.travel_hourly);
        dispatch(setHourly(data.service.travel_hourly));
      }
      // if (data?.service?.guide) {
      //   console.log("guide Data:", data?.service?.guide); // Print to console
      //   dispatch(setbookedGuide(data.service.guide));
      // }

      return data; // Pass the `data` object to the fulfilled case
    } catch (error) {
      console.error("API Error:", error);
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const BookingSlice = createSlice({
  name: "bookings",
  initialState: {
    hotel: [],
    status: "idle",
    error: null,
    searchLocation: [],
    checkIn: "",
    checkOut: "",
    destination: [],
    guests: {
      adults: "",
      children: "",
      infant: "",
      adultGenders: [],
      childrenAges: [],
      maleCount: 0,
      femaleCount: 0,
    },
    tourId: null,
    id: null,
    bookings: [],
    dateService: [],
    attractionService: [],
  },
  reducers: {
    setSearchLocation: (state, action) => {
      console.log('Booking Slice',action.payload);
      
      state.searchLocation = Array.isArray(action.payload)
        ? action.payload
        : [action.payload];
    },
    setCheckIn: (state, action) => {
      state.checkIn = action.payload;
    },
    setCheckOut: (state, action) => {
      state.checkOut = action.payload;
    },
    setGuest: (state, action) => {
      const {
        adults,
        children,
        infant,
        adultGenders = [],
        childrenAges = [],
      } = action.payload;

      state.guests.adults = adults ?? state.guests.adults;
      state.guests.children = children ?? state.guests.children;
      state.guests.infant = infant ?? state.guests.infant;

      state.guests.adultGenders = adultGenders;
      state.guests.childrenAges = childrenAges;

      state.guests.maleCount = adultGenders.filter(
        (gender) => gender === "Male"
      ).length;
      state.guests.femaleCount = adultGenders.filter(
        (gender) => gender === "Female"
      ).length;
    },
    setDateService: (state, action) => {
      state.dateService = action.payload; // Store date_service in state
    },
    setAttractionService: (state, action) => {
      state.attractionService = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchBookingid.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchBookingid.fulfilled, (state, action) => {
        state.status = "succeeded";

        const data = action.payload;

        state.tourId = data.tour_id || null;
        state.id = data.tour_id || null;

        // Log the city from the API response
        const city = data?.city || data?.data?.city;
        if (city) {
          // console.log("City from API Response:", city);
        }

        const newBooking = {
          tourId: data.tour_id,
          checkIn: state.checkIn,
          checkOut: state.checkOut,
          pax: state.guests.adults + state.guests.children,
          destination: data.destination || "",
        };

        state.bookings.push(newBooking);
      })
      .addCase(fetchBookingid.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || action.error.message;
        state.tourId = null;
      });
  },
});

export const { setSearchLocation, setCheckIn, setCheckOut, setGuest } =
  BookingSlice.actions;

export default BookingSlice.reducer;

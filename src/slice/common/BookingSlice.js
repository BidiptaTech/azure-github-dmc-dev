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
import { selectDmcId } from "../dmc/dmcSlice";

export const fetchBookingid = createAsyncThunk(
  "fetchBookingid",
  async (_, { getState, rejectWithValue, dispatch }) => {
    const state = getState().bookings; // Access the bookings slice
    const authState = getState().auth; // Access the auth slice

    try {
      // No longer creating a tour here. Return a safe payload with tour_id null
      const { searchLocation, checkIn, checkOut, guests } = state;
      const { maleCount, femaleCount } = guests;

      const countryCodeToName = {};
      if (authState.user_country && Array.isArray(authState.user_country)) {
        authState.user_country.forEach((country) => {
          if (country && country.name && country.code) {
            countryCodeToName[country.code] = country.name;
            countryCodeToName[country.code.toLowerCase()] = country.name;
          }
        });
      }

      const data = {
        tour_id: null,
        destination: (searchLocation || [])
          .map((location) => countryCodeToName[location] || location)
          .join(", "),
        check_in: checkIn,
        check_out: checkOut,
        random_dmc_id: null,
        service: {},
        city: null,
        meta: {
          adult: guests.adults,
          child: guests.children,
          infant: guests.infant,
          male: maleCount,
          female: femaleCount,
          children_ages: guests.childrenAges.join(", "),
        },
      };

      return data;
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
          dmc_id: data.random_dmc_id || "",
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

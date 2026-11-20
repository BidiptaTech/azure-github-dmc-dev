import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import { BASE_URL } from "@/services/api";
import Cookies from "js-cookie";

const EMPTY_CITY_DATA = {
  hotels: [],
  attractions: [],
  restaurants: [],
  guides: [],
  vehicles: [],
  ports: [],
};

/** Detect city-keyed enquiry_lists payload vs legacy flat shape */
const normalizeEnquiryListData = (payload) => {
  if (!payload || typeof payload !== "object") {
    return { byCity: {}, cities: [] };
  }

  const hasFlatHotels = Array.isArray(payload.hotels);
  const cityEntries = Object.entries(payload).filter(
    ([, value]) =>
      value &&
      typeof value === "object" &&
      !Array.isArray(value) &&
      (Array.isArray(value.hotels) ||
        Array.isArray(value.attractions) ||
        Array.isArray(value.restaurants) ||
        Array.isArray(value.guides) ||
        Array.isArray(value.vehicles) ||
        Array.isArray(value.ports))
  );

  // New multi-city format: { Batam: { hotels, ... }, Singapore: { ... } }
  if (!hasFlatHotels && cityEntries.length > 0) {
    const byCity = {};
    cityEntries.forEach(([city, data]) => {
      byCity[city] = {
        hotels: data.hotels || [],
        attractions: data.attractions || [],
        restaurants: data.restaurants || [],
        guides: data.guides || [],
        vehicles: data.vehicles || [],
        ports: data.ports || [],
      };
    });
    return { byCity, cities: Object.keys(byCity) };
  }

  // Legacy flat format — wrap under a single synthetic key
  const cityName =
    (typeof payload.city === "string" && payload.city) || "Selected City";
  return {
    byCity: {
      [cityName]: {
        hotels: payload.hotels || [],
        attractions: payload.attractions || [],
        restaurants: payload.restaurants || [],
        guides: payload.guides || [],
        vehicles: payload.vehicles || [],
        ports: payload.ports || [],
      },
    },
    cities: [cityName],
  };
};

const applyActiveCityLists = (state, cityName) => {
  const cityData =
    (cityName && state.byCity?.[cityName]) || EMPTY_CITY_DATA;
  state.hotels = cityData.hotels || [];
  state.attractions = cityData.attractions || [];
  state.restaurants = cityData.restaurants || [];
  state.guides = cityData.guides || [];
  state.vehicles = cityData.vehicles || [];
  state.ports = cityData.ports || [];
};

const initialState = {
  ...EMPTY_CITY_DATA,
  byCity: {},
  cities: [],
  activeCity: null,
  loading: false,
  error: null,
};

export const fetchEnquiryList = createAsyncThunk(
  "enquiryList/fetchEnquiryList",
  async ({ country, city }, { rejectWithValue, getState }) => {
    try {
      if (!country || !city || country === "undefined" || city === "undefined") {
        return rejectWithValue("Please provide valid country and city values");
      }

      const state = getState();
      const dmcState = state.dmc;
      const selectedDmcIds = dmcState?.selectedDmcIds || [];

      const authToken = Cookies.get("authToken");

      if (!authToken) {
        return rejectWithValue("Authentication token is missing");
      }

      const response = await axios.get(`${BASE_URL}/enquiry_lists`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        params: {
          country,
          city,
          dmc_ids:
            (selectedDmcIds.length > 0 && JSON.stringify(selectedDmcIds)) ||
            null,
        },
      });
      console.log("API Response:", response.data);

      if (response.data && response.data.success) {
        return response.data.data;
      }

      return rejectWithValue(
        response.data?.message || "Failed to fetch enquiry list"
      );
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || error.message || "Network error"
      );
    }
  }
);

const enquiryListSlice = createSlice({
  name: "enquiryList",
  initialState,
  reducers: {
    clearEnquiryList: (state) => {
      state.hotels = [];
      state.attractions = [];
      state.restaurants = [];
      state.guides = [];
      state.vehicles = [];
      state.ports = [];
      state.byCity = {};
      state.cities = [];
      state.activeCity = null;
      state.error = null;
    },
    setActiveEnquiryCity: (state, action) => {
      const cityName = action.payload;
      if (!cityName) return;
      // Allow selecting a city even before lists load (UI tabs from search)
      state.activeCity = cityName;
      applyActiveCityLists(state, cityName);
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchEnquiryList.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchEnquiryList.fulfilled, (state, action) => {
        state.loading = false;
        const { byCity, cities } = normalizeEnquiryListData(action.payload);
        state.byCity = byCity;
        state.cities = cities;

        // Keep current active city if still present; otherwise first city
        const nextActive =
          (state.activeCity && byCity[state.activeCity]
            ? state.activeCity
            : cities[0]) || null;
        state.activeCity = nextActive;
        applyActiveCityLists(state, nextActive);
      })
      .addCase(fetchEnquiryList.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
  },
});

export const { clearEnquiryList, setActiveEnquiryCity } =
  enquiryListSlice.actions;
export default enquiryListSlice.reducer;

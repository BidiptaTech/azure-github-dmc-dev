import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL } from "@/services/api";

// Define the initial state
const initialState = {
  cities: [],
  cityCountryResults: [], // Results from fetchCityCountry
  selectedCities: [], // Selected cities with their countries (multi-select)
  loading: false,
  error: null,
};

// Create async thunk for fetching cities by country
export const fetchCitiesByCountry = createAsyncThunk(
  "cities/fetchCitiesByCountry",
  async (country, { rejectWithValue }) => {
    try {
      // Get the auth token from cookies
      const token = Cookies.get("authToken");
      
      if (!token) {
        return rejectWithValue("No authentication token found");
      }

      // API endpoint URL for getting cities
      const response = await axios.get(
        `${BASE_URL}/get-cities`,
        {
          headers: {
            "Country": country,
            "Authorization": `Bearer ${token}`
          }
        }
      );
      console.log("response from citiesSlice", response.data);

      // Check if the response is successful and has the expected format
      if (response.data && response.data.cities) {
        return response.data.cities;
      } else {
        return rejectWithValue(response.data.message || "Failed to fetch cities or invalid response format");
      }
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || "Network error when fetching cities"
      );
    }
  }
);

export const fetchCityCountry = createAsyncThunk(
  "cities/fetchCityCountry",
  async (search_term, { rejectWithValue }) => {
    try {
      const token = Cookies.get("authToken");
      if (!token) {
        return rejectWithValue("No authentication token found");
      }
      const response = await axios.get(`${BASE_URL}/city-country`, {
        params: {
          search: search_term
        },
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });
      console.log("response from citiesSlice", response.data);
      if (response.data && response.data.results) {
        return response.data.results;
      } else {
        return rejectWithValue(response.data.message || "Failed to fetch cities or invalid response format");
      }
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || "Network error when fetching city country");
    }
  }
);

// Create the cities slice
const citiesSlice = createSlice({
  name: "cities",
  initialState,
  reducers: {
    clearCities: (state) => {
      state.cities = [];
      state.error = null;
    },
    addSelectedCity: (state, action) => {
      const city = action.payload;
      // Check if city is not already selected (by city_id)
      const exists = state.selectedCities.some(
        (selected) => selected.city_id === city.city_id
      );
      if (!exists) {
        state.selectedCities.push(city);
      }
    },
    removeSelectedCity: (state, action) => {
      const cityId = action.payload;
      state.selectedCities = state.selectedCities.filter(
        (city) => city.city_id !== cityId
      );
    },
    clearSelectedCities: (state) => {
      state.selectedCities = [];
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchCitiesByCountry.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCitiesByCountry.fulfilled, (state, action) => {
        state.loading = false;
        state.cities = action.payload;
      })
      .addCase(fetchCitiesByCountry.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchCityCountry.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCityCountry.fulfilled, (state, action) => {
        state.loading = false;
        state.cityCountryResults = action.payload;
      })
      .addCase(fetchCityCountry.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
        state.cityCountryResults = [];
      });
  },
});

// Export actions and reducer
export const { clearCities, addSelectedCity, removeSelectedCity, clearSelectedCities } = citiesSlice.actions;
export default citiesSlice.reducer; 
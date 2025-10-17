import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import { BASE_URL } from "@/services/api";

// Define the initial state
const initialState = {
  cities: [],
  loading: false,
  error: null,
};

// Create async thunk for fetching cities by country
export const fetchCitiesByCountry = createAsyncThunk(
  "cities/fetchCitiesByCountry",
  async (country, { rejectWithValue }) => {
    try {
      // API endpoint URL for getting cities
      const response = await axios.get(
        `${BASE_URL}/get-cities`,
        {
          headers: {
            "Country": country
          }
        }
      );

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

// Create the cities slice
const citiesSlice = createSlice({
  name: "cities",
  initialState,
  reducers: {
    clearCities: (state) => {
      state.cities = [];
      state.error = null;
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
      });
  },
});

// Export actions and reducer
export const { clearCities } = citiesSlice.actions;
export default citiesSlice.reducer; 
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";

// Async thunk for fetching hotels
export const fetchHotels = createAsyncThunk(
  "hotels/fetchHotels",
  async (searchLocation, { rejectWithValue }) => {
    try {
      console.log("search location = ", searchLocation);
      const response = await axios.get(
        `http://localhost:5000/api/hotels`
      );
      console.log("API response:", response.data); // Log the entire response

      return response.data; // Make sure this contains the hotel data you expect
    } catch (error) {
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const hotelSlice = createSlice({
  name: "hotels",
  initialState: {
    hotels: [], // Ensure this is an empty array initially
    status: "idle",
    error: null,
    searchLocation: "", // Optional: if you're using this for search functionality
  },
  reducers: {
    setSearchLocation: (state, action) => {
      state.searchLocation = action.payload;
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
        state.hotels = Array.isArray(action.payload) ? action.payload : action.payload.hotels || [];
      })
      .addCase(fetchHotels.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || action.error.message;
      });
  },
});


export const { setSearchLocation } = hotelSlice.actions;
export default hotelSlice.reducer;

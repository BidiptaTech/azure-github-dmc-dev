// src/features/viewDetails/viewDetailsSlice.js
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL } from "@/services/api";

// Async thunk to fetch hotel bookings
export const fetchViewDetails = createAsyncThunk(
  "viewDetails/fetchViewDetails",
  async ({ tour_id }, { rejectWithValue }) => {
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const response = await axios.get(
              `${BASE_URL}/tour-details?tour_id=${tour_id}`,
        {
            headers: {
              Authorization: `Bearer ${authToken}`,
              "Content-Type": "application/json",
              "agent-id": AgentId,
             
            },
          }
      );
       console.log("201Fetched Data:", response.data);
      return response.data; // Adjust based on the actual API response structure
    } catch (error) {
      console.error("Fetch Error:", error.response?.data || error.message); // Log error to console
      return rejectWithValue(
        error.response?.data || "Failed to fetch bookings"
      );
    }
  }
);

const viewDetailsSlice = createSlice({
  name: "viewDetails",
  initialState: {
    bookings: [],
    status: "idle", // 'idle' | 'loading' | 'succeeded' | 'failed'
    error: null,
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchViewDetails.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchViewDetails.fulfilled, (state, action) => {
        state.status = "succeeded";
       
        
        state.bookings = action.payload;
      })
      .addCase(fetchViewDetails.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload;
      });
  },
});

export default viewDetailsSlice.reducer;

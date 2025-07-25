import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import moment from "moment";
import getUserCountry from "./getUserCountry"; // Import the function to get user country
import { logoutUser } from "../common/authSlices"; // Adjust the import path
import { setAuthenticated } from "@/pages/login/loginSlice";
import { BASE_URL } from "@/services/api";

// Async thunk for fetching tours
export const fetchLists = createAsyncThunk(
  "Lists/fetchLists",
  async (agentId =null, { rejectWithValue, dispatch }) => {
    console.log(agentId,"agentIdd");
    
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
      let userCountry = Cookies.get("userCountry");

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Fetch the current country
      const currentCountry = await getUserCountry();

      // Check if the country has changed
      if (currentCountry && currentCountry !== userCountry) {
        // Update the cookie if the country has changed
        Cookies.set("userCountry", currentCountry, {
          expires: 7, // Set the cookie to expire in 7 days
          secure: true,
          sameSite: "Strict",
        });
        userCountry = currentCountry; // Update the local variable
      }

      const response = await axios.get(`${BASE_URL}/tour-list`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
          "user-country": userCountry, // Use the updated country
        },
        params: agentId ? { 'agent_id': agentId } : {}
      });

      return response.data.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
        dispatch(setAuthenticated(false));
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const listSlice = createSlice({
  name: "lists",
  initialState: {
    lists: [],
    pendingTours: [],
    upcomingTours: [],
    completedTours: [],
    status: "idle",
    error: null,
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchLists.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchLists.fulfilled, (state, action) => {
        state.status = "succeeded";
        const today = moment();
        state.lists = action.payload.filter((tour) =>
          moment(tour.check_out_time, "DD/MM/YYYY").isSameOrBefore(today, "day")
        );

        // Convert today's date into a moment object for proper comparison

        state.pendingTours = action.payload.filter((tour) =>
          moment(tour.check_in_time, "DD/MM/YYYY").isAfter(today, "day")
        );

        state.upcomingTours = action.payload.filter(
          (tour) =>
            moment(tour.check_in_time, "DD/MM/YYYY").isSameOrBefore(
              today,
              "day"
            ) && moment(tour.check_out_time, "DD/MM/YYYY").isAfter(today, "day")
        );

        state.completedTours = action.payload.filter(
          (tour) =>
            (tour.status === 1 &&
              moment(tour.check_in_time, "DD/MM/YYYY").isAfter(today)) ||
            (tour.editOff === 1 &&
              moment(tour.check_in_time, "DD/MM/YYYY").isAfter(today))
        );
      })
      .addCase(fetchLists.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || "Failed to fetch tour lists.";
      });
  },
});

export default listSlice.reducer;

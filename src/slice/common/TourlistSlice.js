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
  async (params = {}, { getState,rejectWithValue, dispatch }) => {
    const { agentId = null, start = 0, limit = 30, reset = false, type: paramType } = params;
    const stateType = getState().lists.type;
    const agentId1 = getState().editing.agentId;
    const agent=agentId1 || agentId;
    // Prioritize the type from params over the state type
    const type = paramType || stateType;
    console.log(agentId, "agentIdd", start, limit, reset, "paramType:", paramType, "stateType:", stateType, "finalType:", type);
    
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
        params: {
          ...(agent && { 'agent_id': agent }),
          ...(start !== undefined && { start }),
          ...(limit !== undefined && { limit }),
          ...(type !== undefined && { type })
        }
      });

      return { data: response.data.data, reset, start, limit };
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
    start: 0,
    limit: 30,
    type: null,
  },
  reducers: {
    setTourType: (state, action) => {
      state.type = action.payload;
    }
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchLists.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchLists.fulfilled, (state, action) => {
        state.status = "succeeded";
        const { data: payload, reset, start, limit } = action.payload;
        const today = moment();
        
        console.log('Redux slice received data:', {
          payloadLength: payload?.length || 0,
          reset,
          start,
          limit,
          currentType: state.type,
          existingListsLength: state.lists.length
        });
        
        // Update start and limit in state
        if (start !== undefined) state.start = start;
        if (limit !== undefined) state.limit = limit;
        
        // Helper functions to filter tours by category
        const filterPendingTours = (tours) => tours.filter((tour) =>
          moment(tour.check_in_time, "DD/MM/YYYY").isAfter(today, "day")
        );
        
        const filterUpcomingTours = (tours) => tours.filter(
          (tour) =>
            moment(tour.check_in_time, "DD/MM/YYYY").isSameOrBefore(
              today,
              "day"
            ) && moment(tour.check_out_time, "DD/MM/YYYY").isAfter(today, "day")
        );
        
        const filterCompletedTours = (tours) => tours.filter(
          (tour) =>
            (tour.status === 1 &&
              moment(tour.check_in_time, "DD/MM/YYYY").isAfter(today)) ||
            (tour.editOff === 1 &&
              moment(tour.check_in_time, "DD/MM/YYYY").isAfter(today))
        );
        
        const filterLists = (tours) => tours.filter((tour) =>
          moment(tour.check_out_time, "DD/MM/YYYY").isSameOrBefore(today, "day")
        );

        // Determine which list to update based on state.type
        let targetListKey;
        let currentFilterFunction;

        switch (state.type) {
          case "past":
            targetListKey = "lists"; // Deleted.jsx uses 'lists'
            currentFilterFunction = filterLists;
            break;
          case "upcoming":
            targetListKey = "pendingTours"; // Pending.jsx uses 'pendingTours'
            currentFilterFunction = filterPendingTours;
            break;
          case "ongoing":
            targetListKey = "upcomingTours"; // Upcoming.jsx uses 'upcomingTours'
            currentFilterFunction = filterUpcomingTours;
            break;
          case "completed":
            targetListKey = "completedTours";
            currentFilterFunction = filterCompletedTours;
            break;
          default:
            targetListKey = "lists"; // Fallback
            currentFilterFunction = (tours) => tours; // No filtering
            console.warn("Unknown tour type:", state.type);
            break;
        }

        console.log('Redux slice processing data for type:', state.type, 'targetList:', targetListKey);

        if (reset) {
          state[targetListKey] = currentFilterFunction(payload);
          console.log(`Resetting ${targetListKey}:`, { newLength: state[targetListKey].length });
        } else {
          const existingIds = new Set(state[targetListKey].map(tour => tour.id));
          const newTours = payload.filter(tour => !existingIds.has(tour.id));

          if (newTours.length > 0) {
            const newFilteredTours = currentFilterFunction(newTours);
            const previousLength = state[targetListKey].length;
            state[targetListKey] = [...state[targetListKey], ...newFilteredTours];
            
            console.log(`Accumulated data for ${targetListKey}:`, {
              newToursCount: newTours.length,
              newFilteredToursCount: newFilteredTours.length,
              previousLength: previousLength,
              totalLength: state[targetListKey].length,
              type: state.type
            });
          } else {
            console.log(`No new tours to add to ${targetListKey} - all were duplicates or filtered out.`);
          }
        }
      })
      .addCase(fetchLists.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || "Failed to fetch tour lists.";
      });
  },
});
export const { setTourType } = listSlice.actions;
export default listSlice.reducer;

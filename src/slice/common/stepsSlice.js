

// export default stepsSlice.reducer;
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { logoutUser } from "../common/authSlices";
import { BASE_URL } from "@/services/api";

// Async thunk for updating status
export const statusUpdate = createAsyncThunk(
  "steps/statusUpdate",
  async (_, { getState, rejectWithValue }) => {
    const state = getState().steps;

    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        throw new Error("Authorization or AgentId is missing.");
      }

      const { id, stepStatus, type } = state;

      if (!id || !Object.keys(stepStatus).length) {
        throw new Error("Tour ID or step status is missing.");
      }

      const [key, value] = Object.entries(stepStatus)[0];

      const formData = new FormData();
      formData.append("tour_id", id);
      formData.append(key, 1);
      formData.append("status", value);
      formData.append("type", type);
      formData.append("tour_type", 1);

      const response = await axios.post(`${BASE_URL}/tour-status`, formData, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "multipart/form-data",
          "agent-id": AgentId,
        },
      });
      // console.log("response", response.data);

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        // console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Slice definition
const stepsSlice = createSlice({
  name: "steps",
  initialState: {
    id: null, // Tour ID
    stepStatus: {}, // Only one key-value pair
    stepStatus1: {
      hotel: 0,
      port: 0,
      attraction: 0,
      guide: 0,
      restaurent: 0,
      travel: 0,
    },
    currentStep: 0, // Tracks the index of the current step
    type: "",
    status: 0,
    active_status: 0,
    loading: false, // API call loading state
    error: null, // API call error
  },
  reducers: {
    // Set tour ID
    setTourId: (state, action) => {
      state.id = action.payload;
      //  console.log("id", state.id);
    },
    // Update the status of the current step
    updateStepStatus: (state, action) => {
      const { key, status } = action.payload;
      state.stepStatus = { [key]: status }; // Override with the new key-value pair
    },
    setType: (state, action) => {
      state.type = action.payload;
    },
    // Reset all states
    resetSteps: (state) => {
      state.stepStatus = {};
      state.currentStep = 0;
      state.id = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(statusUpdate.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(statusUpdate.fulfilled, (state, action) => {
        state.loading = false;
        const { data } = action.payload;
        const { active_task } = action.payload;
        const { active_task_status } = action.payload;
        //const { active_task_status } = action.payload;
        // Update stepStatus with API response
        state.stepStatus1 = { ...state.stepStatus1, ...data };
        const level = [
          "hotel",
          "port",
          "attraction",

          "guide",
          "restaurent",
          "travel",
        ];
        // console.log("active", active_task);
        const currentStepIndex = level.indexOf(active_task);
        state.active_status = active_task_status;
        state.currentStep = currentStepIndex !== -1 ? currentStepIndex : null;
      })
      .addCase(statusUpdate.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
  },
});

// Export actions and reducer
export const { setTourId, updateStepStatus, resetSteps, setType } =
  stepsSlice.actions;
export default stepsSlice.reducer;

import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { setTourIdd } from "./authSlices";
import { setAttractionService } from "../attractions/attractionSlice";
import { setRestaurantsService } from "../restaurant/RestaurantsSlice";
import { setDateService } from "../common/dateServicesSlice";
import { setCity } from "./citySlice";
import { setSelectedDmcId, setDmcFromAuth } from "../dmc/dmcSlice";
import {
  setEntryport,
  setExitport,
  updateFormData,
  updateFormData1,
} from "../port/pickupDropSlice";
import { setHourly, setPointToPoint, setZone } from "../localtour/Localslice";
import { setbookedGuide } from "../tourguide/guideslice";
import { logoutUser } from "../common/authSlices";
import { setHotelBooking, setHotelService } from "../hotel/hotelSlice";
import { BASE_URL } from "@/services/api";

// 🟢 Async thunk to fetch tour edit data
export const fetchEditid = createAsyncThunk(
  "editing/fetchEditid",
  async (_, { getState, rejectWithValue, dispatch }) => {
    const { tourId } = getState().editing;

    // Check if tourId exists before proceeding
    if (!tourId) {
      return rejectWithValue("Tour ID is missing");
    }

    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const response = await axios.get(`${BASE_URL}/edit-tour`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "application/json",
          "agent-id": AgentId,
          "tour-id": tourId,
        },
      });

      console.log("Edit API Response:", response.data);
      const { tour_display_id, data } = response.data;
      const { tour_id, agent_id } = data || {};

      // Only dispatch if data/properties exist
      if (data?.cities) {
        dispatch(setCity(data.cities));
      }

      if (tour_id) {
        dispatch(setTourIdd(tour_id));
      }
      if (agent_id) {
        dispatch(setAgentId(agent_id));
      }

      // Safely dispatch hotel service data
      dispatch(setHotelService(data?.service?.hotel || []));

      // Safely dispatch other service data
      dispatch(setAttractionService(data?.service?.attraction || []));
      dispatch(setRestaurantsService(data?.service?.restaurant || []));
      dispatch(setDateService(data?.service?.date_service || []));

      // Handle entry_port data
      if (Array.isArray(data?.service?.entry_port)) {
        const formData = data.service.entry_port.flatMap((entry) =>
          Object.entries(entry)
            .map(([key, value]) => {
              switch (key) {
                case "entrypickup":
                  return { "Pick Up Location": value };
                case "entrydropoff":
                  return { "Drop Off Location": value };
                case "pickupdate":
                  return { Date: value };
                case "entrytime":
                  return { Time: value };
                case "vehicles_name":
                  return { Vehicle: value };
                case "adults":
                  return { Adult: value };
                case "children":
                  return { Children: value };
                default:
                  return null;
              }
            })
            .filter(Boolean)
        );

        // Only dispatch if formData has items
        if (formData.length > 0) {
          dispatch(setEntryport(data.service.entry_port));
          dispatch(updateFormData(formData));
        }
      }

      // Similarly check exit_port data before dispatching
      if (Array.isArray(data?.service?.exit_port)) {
        const formData1 = data.service.exit_port.flatMap((exit) =>
          Object.entries(exit)
            .map(([key, value]) => {
              switch (key) {
                case "exitpickup":
                  return { "Pick Up Location": value };
                case "exitdropoff":
                  return { "Drop Off Location": value };
                case "exitpickupdate":
                  return { Date: value };
                case "exittime":
                  return { Time: value };
                case "vehicles_name":
                  return { Vehicle: value };
                case "adults":
                  return { Adults: value };
                case "children":
                  return { Children: value };
                default:
                  return null;
              }
            })
            .filter(Boolean)
        );

        // Only dispatch if formData1 has items
        if (formData1.length > 0) {
          dispatch(setExitport(data.service.exit_port));
          dispatch(updateFormData1(formData1));
        }
      }

      // Safely dispatch other data
      dispatch(setPointToPoint(data.service?.travel_point || []));
      dispatch(setHourly(data.service?.travel_hourly || []));
      dispatch(setZone(data.service?.local_transport || []));
      dispatch(setbookedGuide(data.service?.guide || []));

      // Handle DMC ID, logo, and company name from response
      if (data?.dmc_id) {
        console.log('🎯 EditSlice: Setting DMC data from edit tour response');
        console.log('🎯 EditSlice: dmc_id:', data.dmc_id);
        console.log('🎯 EditSlice: dmc_logo:', data.dmc_logo);
        console.log('🎯 EditSlice: dmc_company_name:', data.dmc_company_name);
        
        dispatch(setSelectedDmcId({
          dmcId: data.dmc_id,
          dmcData: {
            id: `dmc-edit-${data.dmc_id}`,
            dmcId: data.dmc_id,
            name: data.dmc_company_name || `DMC ${data.dmc_id}`,
            location: 'Edit-selected',
            logo: data.dmc_logo || '',
            rating: 4.5,
            description: 'DMC from edit tour response',
            originalData: { 
              dmcId: data.dmc_id,
              logo: data.dmc_logo,
              company_name: data.dmc_company_name
            }
          }
        }));
      } else if (data?.dmc_id === null) {
        console.log('🎯 EditSlice: DMC ID is null in edit tour response');
      }
      
      // Alternative approach: Also dispatch setDmcFromAuth for direct DMC data storage
      if (data?.dmc_id && (data?.dmc_logo || data?.dmc_company_name)) {
        console.log('🎯 EditSlice: Also dispatching setDmcFromAuth for direct DMC data storage');
        dispatch(setDmcFromAuth({
          dmcId: data.dmc_id,
          dmcLogo: data.dmc_logo || null,
          dmcCompanyName: data.dmc_company_name || null
        }));
      }

      // Return data for unwrap()
      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Async thunk to delete a tour
export const deleteTour = createAsyncThunk(
  "editing/deleteTour",
  async (tourId, { rejectWithValue, dispatch }) => {
    // Accept tourId as argument
    console.log("🚀 tourId to be deleted:", tourId); // ✅ This will now show the correct ID

    if (!tourId) {
      console.error("❌ tourId is missing!");
      return rejectWithValue("Tour ID is required.");
    }

    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
      console.log("authToken", authToken);
      console.log("AgentId", AgentId);

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const response = await axios.post(
        `${BASE_URL}/tour-delete`,
        { tour_id: tourId },
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
          },
        }
      );

      console.log("✅ Delete Tour Response:", response.data);

      dispatch(setTourId1(null)); // Clear tourId after deletion
      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// 📝 Edit slice
const EditSlice = createSlice({
  name: "editing",
  initialState: {
    tourId: null,
    tourData: null,
    status: null,
    error: null,
    agentId: null,
  },
  reducers: {
    setTourId1: (state, action) => {
      console.log("Set TourId:", action.payload);
      state.tourId = action.payload;
    },
    setAgentId: (state, action) => {
      state.agentId = action.payload;
      console.log("Agent ID set:", action.payload);
    },
  },
  extraReducers: (builder) => {
    builder
      // 🔄 Fetch Edit Tour
      .addCase(fetchEditid.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchEditid.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.tourData = action.payload;
      })
      .addCase(fetchEditid.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || action.error.message;
        state.tourId = null;
      })

      // 🗑️ Delete Tour
      .addCase(deleteTour.pending, (state) => {
        state.status = "deleting";
        state.error = null;
      })
      .addCase(deleteTour.fulfilled, (state, action) => {
        state.status = "deleted";
        state.tourData = null;
        state.tourId = null;
      })
      .addCase(deleteTour.rejected, (state, action) => {
        state.status = "delete_failed";
        state.error = action.payload || action.error.message;
      });
  },
});

export const { setTourId1, setAgentId } = EditSlice.actions;
export default EditSlice.reducer;

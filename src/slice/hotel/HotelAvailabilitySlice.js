import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { useSelector } from "react-redux";
import { BASE_URL } from "@/services/api";
import { setHotelDetails, setHotelPolicies } from "./HotelDetailsSlice";

// Initial state
const initialState = {
  id: null,
  roomDatas: [],
  isQuantityVisible: {}, // For toggling quantity visibility
  status: "idle",
  error: null,
  tour_id:0,
};

// Helper function to extract policy data
const extractPolicyData = (apiResponse) => {
  if (!apiResponse || !apiResponse.policy) {
    console.warn("No policy data found in API response");
    return [];
  }
  
  return apiResponse.policy;
};

// Thunk for fetching room data
export const fetchRoomData = createAsyncThunk(
  "rooms/fetchRoomData",
  async ({ id, tour_id, priceMode,priceModeId,dmc_id }, { rejectWithValue, dispatch }) => {
    // console.log(tour_id,"tour ID");
    // console.log(priceModeId,"pricmodeID");
    console.log(dmc_id,"dmc_id");
    
    try {
      //console.log("API call initiated with ID:", id, "Price Mode:", priceMode);
      
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
     // console.log(AgentId,"agentiddd");
      
      // const dmc_id = Cookies.get("dmc_id"); // Get dmc_id from cookies
      // const mode = Cookies.get("mode"); // Get mode from cookies

      if (!authToken || !AgentId) {
        throw new Error("Authorization, AgentId, dmc_id, or mode are missing.");
      }

      const response = await axios.get(
        `${BASE_URL}/hotel-details?id=${id}&price-mode=${priceMode}&agent-id=${AgentId}&tour-id=${tour_id}&dmc_id=${dmc_id}`,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            // "agent-id": AgentId,
            // "tour-id": tour_id,
          },
        }
      );
      
      const responseData = response.data;
      console.log("API response:", responseData);
      
      // Dispatch hotel details
      dispatch(setHotelDetails(responseData));
      
      // Extract and dispatch policy data
      const policyData = extractPolicyData(responseData);
      console.log("Extracted policy data:", policyData);
      
      // Dispatch only if policy data exists
      if (policyData && policyData.length > 0) {
        dispatch(setHotelPolicies(policyData));
      } else {
        console.warn("No policy data to dispatch");
      }
      
      return responseData;
    } catch (error) {
      console.error("Error fetching room data:", error);
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);


const roomSlice = createSlice({
  name: "rooms",
  initialState,

  reducers: {
    setId: (state, action) => {
      state.id = action.payload;
      //console.log("aaaaa", state.id);
    },
    settourid:(state,action)=>{
      state.tour_id=action.payload;
     // console.log("ba", state.tour_id);
    },
    updateQuantity: (state, action) => {
      const { roomType, optionId, quantity } = action.payload;
      const room = state.roomDatas.find((room) => room.room_type === roomType);
     // console.log("rooooom", room);

      if (room && room.roomOptions) {
        const option = room.roomOptions.find(
          (option) => option.id === optionId
        );
        if (option) {
          option.quantity += quantity;
          if (option.quantity < 0) option.quantity = 0;
        }
      }
    },
    incrementQuantity: (state, action) => {
      const { roomType, optionId } = action.payload;
      const room = state.roomDatas.find((room) => room.room_type === roomType);
      if (room && room.roomOptions) {
        const option = room.roomOptions.find(
          (option) => option.id === optionId
        );
        if (option) option.quantity += 1;
      }
    },
    decrementQuantity: (state, action) => {
      const { roomType, optionId } = action.payload;
      const room = state.roomDatas.find((room) => room.room_type === roomType);
      if (room && room.roomOptions) {
        const option = room.roomOptions.find(
          (option) => option.id === optionId
        );
        if (option && option.quantity > 0) {
          option.quantity -= 1;
        }
      }
    },
    toggleQuantityVisibility: (state, action) => {
      const { roomType, optionId } = action.payload;
      const key = `${roomType}-${optionId}`;
      state.isQuantityVisible[key] = !state.isQuantityVisible[key];
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchRoomData.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchRoomData.fulfilled, (state, action) => {
        // console.log(
        //   "Thunk fulfilled. Room data updated in state:",
        //   action.payload
        // );
        state.status = "succeeded";
        state.roomDatas = action.payload || [];

        console.log("Room data updated in state:", state.roomDatas);
      })
      .addCase(fetchRoomData.rejected, (state, action) => {
        //console.error("Thunk rejected. Error:", action.payload);
        state.status = "failed";
        state.error = action.payload;
      });
  },
});

// Middleware for persisting roomDatas in localStorage
export const roomSliceMiddleware = (storeAPI) => (next) => (action) => {
  const result = next(action);

  // Persist `roomDatas` to localStorage when it's updated
  const state = storeAPI.getState();
  if (state.rooms?.roomDatas) {
    localStorage.setItem("roomDatas", JSON.stringify(state.rooms.roomDatas));
  }

  return result;
};

export const {
  setId,
  updateQuantity,
  incrementQuantity,
  decrementQuantity,
  toggleQuantityVisibility,
  settourid,
} = roomSlice.actions;

export default roomSlice.reducer;

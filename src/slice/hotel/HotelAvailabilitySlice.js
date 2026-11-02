import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { useSelector } from "react-redux";
import { BASE_URL } from "@/services/api";
import { extractCityNamesFromLocation } from "@/utils/locationFormat";
import { setHotelDetails, setHotelImages, setHotelPolicies } from "./HotelDetailsSlice";

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

const toApiDate = (value) => {
  if (!value) return "";
  if (typeof value === "string" && /^\d{4}-\d{2}-\d{2}/.test(value)) {
    return value.slice(0, 10);
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "";
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${date.getFullYear()}-${month}-${day}`;
};

const amountFrom = (value) => {
  if (value == null || value === "") return 0;
  if (typeof value === "number") return Number.isFinite(value) ? value : 0;
  if (typeof value === "string") {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
  }
  if (typeof value === "object") {
    return amountFrom(value.actual ?? value.total ?? value.amount ?? value.price);
  }
  return 0;
};

const imageUrls = (images) => {
  if (!Array.isArray(images)) return [];
  return images
    .map((image) => (typeof image === "string" ? image : image?.url || image?.image || image?.src || ""))
    .filter(Boolean);
};

const mapOnlineRooms = (rooms, adults) =>
  (Array.isArray(rooms) ? rooms : []).map((room, index) => {
    if (Array.isArray(room?.bed_details) && room.bed_details.length > 0 && room?.single_price) {
      return room;
    }
    const price = amountFrom(room?.price) || amountFrom(room?.currency_converted_price);
    const breakfastIncluded =
      Boolean(room?.breakfast_included) || /breakfast/i.test(String(room?.meal_plan || room?.rate_plan_name || ""));
    const roomName = room?.room_name || room?.room_type || room?.name || `Room ${index + 1}`;
    const occupancy = Number(room?.max_occupancy || room?.max_adult || room?.occupancy || adults || 1) || 1;

    return {
      ...room,
      room_type: roomName,
      room_id: room?.room_id || room?.id || index,
      single_price: price,
      double_price: price,
      rooms_only: breakfastIncluded ? 0 : 1,
      breakfast: breakfastIncluded ? 1 : 0,
      breakfast_price: breakfastIncluded ? 0 : null,
      bed_details: [
        {
          bed_type: roomName,
          bed_id: room?.room_id || room?.rate_plan_id || index,
          max_occupancy: occupancy,
          price,
          extra_bed: 0,
          extra_bed_price: 0,
          baby_cot: 0,
        },
      ],
    };
  });

// Thunk for fetching room data
export const fetchRoomData = createAsyncThunk(
  "rooms/fetchRoomData",
  async ({ id, tour_id, priceMode, priceModeId, dmc_id, online, hotel_id }, { rejectWithValue, dispatch, getState }) => {
    // console.log(tour_id,"tour ID");
    // console.log(priceModeId,"pricmodeID");
    
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

      // If no tour_id yet, include guest details so backend can compute availability
      const hasTourId = tour_id && Number(tour_id) > 0;
      const tourIdParam = hasTourId ? tour_id : '';

      // Pull guest details from state as fallback
      const root = getState();
      const tourDetails = root?.hotels?.tourdetails || {};
      const guests = root?.bookings?.guests || {};
      const adult = tourDetails?.adult ?? guests?.adults ?? 1;
      const child = tourDetails?.child ?? guests?.children ?? 0;
      const infant = tourDetails?.infant ?? guests?.infant ?? 0;
      const male = tourDetails?.male ?? guests?.maleCount ?? 0;
      const female = tourDetails?.female ?? guests?.femaleCount ?? 0;
      const tour_child_adult = 0; // default if not available in state
      const all_pax = Number(adult) + Number(child);

      const guestQuery = hasTourId
        ? ''
        : `&all_pax=${encodeURIComponent(all_pax)}&tour_adult=${encodeURIComponent(adult)}&tour_child=${encodeURIComponent(child)}&tour_infant=${encodeURIComponent(infant)}&tour_male=${encodeURIComponent(male)}&tour_female=${encodeURIComponent(female)}&tour_child_adult=${encodeURIComponent(tour_child_adult)}`;

      if (online) {
        const search = root?.hotels?.searchState || {};
        const guestCounts = root?.common?.guestCounts || {};
        const searchGuests = search.guests || {};
        const city = String(extractCityNamesFromLocation(search.location) || "")
          .split(",")
          .map((part) => part.trim())
          .filter(Boolean)[0] || "";
        const selectedDmcId = dmc_id || root?.dmc?.dmcId;
        const adults = Number(searchGuests.adults ?? searchGuests.Adults ?? guestCounts.Adults ?? adult ?? 1) || 1;
        const rooms = Number(searchGuests.rooms ?? guests.rooms ?? tourDetails?.rooms ?? 1) || 1;

        const response = await axios.get(`${BASE_URL}/online-hotels/details`, {
          params: {
            dmc_id: selectedDmcId != null ? Number(selectedDmcId) : null,
            city,
            hotel_id: hotel_id || id,
            check_in: toApiDate(search.ucheckIn),
            check_out: toApiDate(search.ucheckOut),
            adults,
            rooms,
          },
          headers: {
            Authorization: `Bearer ${authToken}`,
            "agent-id": AgentId,
          },
        });

        const payload = response.data?.data || {};
        const hotel = payload.hotel || {};
        const roomList = payload.rooms?.length ? payload.rooms : hotel.rooms || [];
        const photos = imageUrls(hotel.images);
        const mappedRooms = mapOnlineRooms(roomList, adults);
        const sessionId =
          payload.meta?.session_id ||
          roomList[0]?.booking?.session_id ||
          null;

        dispatch(setHotelDetails({
          ...hotel,
          hotel_id: hotel.hotel_id || hotel_id || id,
          hotel_name: hotel.hotel_name || hotel.name || "",
          address: hotel.address || "",
          description: hotel.description || "",
          image: photos[0] || "",
          site_image: photos,
          data_mode: "online",
          currency: hotel.currency || roomList[0]?.booking?.currency || "SGD",
          session_id: sessionId,
          dmc_id: roomList[0]?.markup?.dmc_id || selectedDmcId,
          supplier_code: payload.meta?.supplier_code || roomList[0]?.supplier_code || hotel.supplier_code,
        }));
        if (photos.length) dispatch(setHotelImages(photos));

        return {
          room_data: mappedRooms,
          image: photos[0] || "",
          tour_adult: adults,
          tour_child: Number(searchGuests.children ?? searchGuests.Children ?? guestCounts.Children ?? child ?? 0) || 0,
          tour_infant: Number(searchGuests.infant ?? guestCounts.Infants ?? infant ?? 0) || 0,
          online: true,
          loaded: true,
          hotel_id: hotel.hotel_id || hotel_id || id,
          session_id: sessionId,
          meta: payload.meta || null,
          hotel,
        };
      }

      const response = await axios.get(
        `${BASE_URL}/hotel-details?id=${id}&price-mode=${priceMode}&agent-id=${AgentId}&tour-id=${tourIdParam}&dmc_id=${dmc_id}${guestQuery}`,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            // "agent-id": AgentId,
            // "tour-id": tour_id, 
          },
        }
      );
      
      const responseData = response.data && typeof response.data === "object" && !Array.isArray(response.data)
        ? { ...response.data, hotel_id: id, online: false, loaded: true, data_mode: "offline" }
        : response.data;
      // console.log("API response:", responseData);
      
      // Dispatch hotel details
      dispatch(setHotelDetails(responseData));
      
      // Extract and dispatch policy data
      const policyData = extractPolicyData(responseData);
      // console.log("Extracted policy data:", policyData);
      
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
    setRoomDatas: (state, action) => {
      state.roomDatas = action.payload || [];
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

        // console.log("Room data updated in state:", state.roomDatas);
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
  setRoomDatas,
} = roomSlice.actions;

export default roomSlice.reducer;

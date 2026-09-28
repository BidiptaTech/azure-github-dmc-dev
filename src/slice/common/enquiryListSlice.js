import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import { BASE_URL } from "@/services/api";
import Cookies from "js-cookie";

// Define the initial state
const initialState = {
  hotels: [],
  attractions: [],
  restaurants: [],
  guides: [],
  vehicles: [],
  ports: [],
  loading: false,
  error: null,
};

// Create async thunk for fetching the list data
export const fetchEnquiryList = createAsyncThunk(
  "enquiryList/fetchEnquiryList",
  async ({ country, city }, { rejectWithValue, getState }) => {
    try {
      // Validate input parameters
      if (!country || !city || country === 'undefined' || city === 'undefined') {
        // console.warn("Invalid country or city provided:", { country, city });
        return rejectWithValue("Please provide valid country and city values");
      }

      const state = getState();
      const dmcState = state.dmc;
      const selectedDmcIds = dmcState?.selectedDmcIds || [];
     // const selectedDmcId = dmcState?.dmcId || null;
      // console.log("FETCH ENQUIRY LIST - Selected DMC IDs:", selectedDmcIds);
      
      // Get auth token
      const authToken = Cookies.get("authToken");
      
      if (!authToken) {
        return rejectWithValue("Authentication token not found");
      }

      // console.log("Making API call with params:", { country, city, dmc_ids: selectedDmcIds });

      const response = await axios.get(`${BASE_URL}/enquiry_lists`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        params: {
          country,
          city,
          dmc_ids: selectedDmcIds.length > 0 && JSON.stringify(selectedDmcIds) || null,
        }
      });
      console.log("API Response:", response.data);  
      // console.log("API Response:", response.data);

      if (response.data && response.data.success) {
        return response.data.data;
      }

      return rejectWithValue(response.data?.message || "Failed to fetch enquiry list");
    } catch (error) {
      // console.error("API Error:", error);
      return rejectWithValue(
        error.response?.data?.message || error.message || "Network error"
      );
    }
  }
);

// Create the enquiry list slice
const enquiryListSlice = createSlice({
  name: "enquiryList",
  initialState,
  reducers: {
    clearEnquiryList: (state) => {
      state.hotels = [];
      state.attractions = [];
      state.restaurants = [];
      state.guides = [];
      state.vehicles = [];
      state.ports = [];
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchEnquiryList.pending, (state) => {
        // console.log("FETCH ENQUIRY LIST - Status: PENDING");
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchEnquiryList.fulfilled, (state, action) => {
        // console.log("FETCH ENQUIRY LIST - Status: FULFILLED with payload:", action.payload);
        state.loading = false;
        state.hotels = action.payload.hotels || [];
        state.attractions = action.payload.attractions || [];
        state.restaurants = action.payload.restaurants || [];
        state.guides = action.payload.guides || [];
        state.vehicles = action.payload.vehicles || [];
        state.ports = action.payload.ports || [];
      })
      .addCase(fetchEnquiryList.rejected, (state, action) => {
        // console.error("FETCH ENQUIRY LIST - Status: REJECTED with error:", action.payload);
        state.loading = false;
        state.error = action.payload;
      });
  },
});

// Export actions and reducer
export const { clearEnquiryList } = enquiryListSlice.actions;
export default enquiryListSlice.reducer; 
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import { BASE_URL } from "@/services/api";
import api from "@/services/api"; // Import the configured axios instance
import Cookies from "js-cookie"; // Import js-cookie to get the auth token

// Define the initial state
const initialState = {
  hotels: [],
  attractions: [],
  restaurants: [],
  guides: [],
  vehicles: [],
  ports: [],
  packaged_attractions: [], // <-- add this
  loading: false,
  error: null,
};

// Create async thunk for fetching the list data
export const fetchEnquiryList = createAsyncThunk(
  "enquiryList/fetchEnquiryList",
  async ({ country, city }, { rejectWithValue }) => {
    const apiUrl = `${BASE_URL}/enquiry_lists`;
    console.log("FETCH ENQUIRY LIST - Starting API call with:", { country, city });
    console.log("FETCH ENQUIRY LIST - API URL:", apiUrl);
    
    try {
      // Get the auth token from cookies (as used in api.js)
      const authToken = Cookies.get("authToken") 
      
      if (!authToken) {
        console.error("FETCH ENQUIRY LIST - No auth token found in cookies or localStorage");
        return rejectWithValue("Authentication token not found");
      }
      
      console.log("FETCH ENQUIRY LIST - Auth token found:", authToken.substring(0, 15) + "...");

      // First approach: Use the configured axios instance
      try {
        console.log("FETCH ENQUIRY LIST - Using configured axios instance");
        
        const response = await api.request(
          "get", 
          "/enquiry_lists", 
          null, 
          {
            params: { country, city }
          }
        );
        
        console.log("FETCH ENQUIRY LIST - Response from configured instance:", response);
        
        if (response.data && response.data.success) {
          console.log("FETCH ENQUIRY LIST - Success with configured instance:", response.data);
          return response.data.data;
        }
        
        console.log("FETCH ENQUIRY LIST - Configured instance didn't return success, trying direct axios");
        throw new Error("First approach failed");
      } catch (configuredError) {
        console.warn("FETCH ENQUIRY LIST - Configured axios approach failed:", configuredError.message);
        
        // Second approach: Direct axios call with headers
        try {
          console.log("FETCH ENQUIRY LIST - Trying direct axios approach");
          const directResponse = await axios.get(apiUrl, {
            headers: {
              Authorization: `Bearer ${authToken}`,
              "Content-Type": "application/json",
              "Accept": "application/json"
            },
            params: {
              country,
              city
            }
          });
          
          console.log("FETCH ENQUIRY LIST - Response from direct axios:", directResponse);
          
          if (directResponse.data && directResponse.data.success) {
            console.log("FETCH ENQUIRY LIST - Success with direct axios:", directResponse.data);
            return directResponse.data.data;
          }
          
          throw new Error(directResponse.data?.message || "API returned unsuccessful response");
        } catch (directError) {
          console.warn("FETCH ENQUIRY LIST - Direct axios approach failed:", directError.message);
          
          // Third approach: Try with URL params
          const urlWithParams = `${apiUrl}?country=${encodeURIComponent(country)}&city=${encodeURIComponent(city)}`;
          console.log("FETCH ENQUIRY LIST - Trying URL params approach:", urlWithParams);
          
          const urlResponse = await axios.get(urlWithParams, {
            headers: {
              Authorization: `Bearer ${authToken}`,
              "Content-Type": "application/json",
              "Accept": "application/json"
            }
          });
          
          console.log("FETCH ENQUIRY LIST - Response from URL params approach:", urlResponse);
          
          if (urlResponse.data && urlResponse.data.success) {
            console.log("FETCH ENQUIRY LIST - Success with URL params approach:", urlResponse.data);
            return urlResponse.data.data;
          }
          
          return rejectWithValue(urlResponse.data?.message || "Failed to fetch list");
        }
      }
    } catch (error) {
      console.error("FETCH ENQUIRY LIST - All approaches failed:", error);
      console.error("FETCH ENQUIRY LIST - Error details:", {
        message: error.message,
        status: error.response?.status,
        statusText: error.response?.statusText,
        responseData: error.response?.data
      });
      
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
      state.packaged_attractions = [];
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchEnquiryList.pending, (state) => {
        console.log("FETCH ENQUIRY LIST - Status: PENDING");
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchEnquiryList.fulfilled, (state, action) => {
        console.log("FETCH ENQUIRY LIST - Status: FULFILLED with payload:", action.payload);
        state.loading = false;
        state.hotels = action.payload.hotels || [];
        state.attractions = action.payload.attractions || [];
        state.restaurants = action.payload.restaurants || [];
        state.guides = action.payload.guides || [];
        state.vehicles = action.payload.vehicles || [];
        state.ports = action.payload.ports || [];
        state.packaged_attractions = action.payload.packaged_attractions || [];
      })
      .addCase(fetchEnquiryList.rejected, (state, action) => {
        console.error("FETCH ENQUIRY LIST - Status: REJECTED with error:", action.payload);
        state.loading = false;
        state.error = action.payload;
      });
  },
});

// Export actions and reducer
export const { clearEnquiryList } = enquiryListSlice.actions;
export default enquiryListSlice.reducer; 
import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from "js-cookie";

import { BASE_URL } from '@/services/api';

// Async thunk for fetching enquiries
export const fetchEnquiries = createAsyncThunk(
  'enquiryList/fetchEnquiries',
  async (params = {}, { getState, rejectWithValue }) => {
    const { agentId = null, start = 0, limit = 30, reset = false } = params;
    
    try {
      const token = Cookies.get("authToken");
      
      if (!token) {
        return rejectWithValue('Authentication token not found');
      }
      
      const response = await axios.get(`${BASE_URL}/listofenquiry`,{
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        params: {
          ...(agentId && { 'agent_id': agentId }),
          ...(start !== undefined && { start }),
          ...(limit !== undefined && { limit })
        }
      });
      
      if (response.data.success) {
        return { 
          data: response.data.enquiries, 
          reset, 
          start, 
          limit 
        };
      } else {
        return rejectWithValue(response.data.message || 'Failed to fetch enquiries');
      }
    } catch (error) {
      return rejectWithValue(error.message || 'An error occurred while fetching data');
    }
  }
);

const initialState = {
  enquiries: [],
  status: 'idle',
  error: null,
  totalCount: 0,
  start: 0,
  limit: 30,
  stats: {
    total: 0,
    withHotel: 0,
    withPickup: 0,
    withPort: 0,
    withAttractions: 0,
    withPackagedAttractions: 0,
    withRestaurants: 0,
    withGuides: 0,
  }
};

const enquiryListSlice = createSlice({
  name: 'enquiryList',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchEnquiries.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
     .addCase(fetchEnquiries.fulfilled, (state, action) => {
  const { data: enquiries, reset, start, limit } = action.payload || {};
  const enquiriesArray = enquiries || [];

  state.status = 'succeeded';
  
  // Update start and limit in state
  if (start !== undefined) state.start = start;
  if (limit !== undefined) state.limit = limit;
  
  // Handle data based on reset flag
  if (reset) {
    // Reset - replace the data
    state.enquiries = enquiriesArray;
  } else {
    // Append the data
    state.enquiries = [...state.enquiries, ...enquiriesArray];
  }
  
  state.totalCount = state.enquiries.length;

  // Calculate stats safely
  state.stats.total = state.enquiries.length;
  state.stats.withHotel = state.enquiries.filter(e => e.hotel).length;
  state.stats.withPickup = state.enquiries.filter(e => e.local_transfer).length;
  state.stats.withPort = state.enquiries.filter(e => e.port).length;
  state.stats.withAttractions = state.enquiries.filter(e => e.attraction).length;
  state.stats.withPackagedAttractions = state.enquiries.filter(e => e.packaged_attractions).length;
  state.stats.withRestaurants = state.enquiries.filter(e => e.restaurant).length;
  state.stats.withGuides = state.enquiries.filter(e => e.guide).length;
})
      .addCase(fetchEnquiries.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Unknown error occurred';
      });
  },
});

export default enquiryListSlice.reducer; 
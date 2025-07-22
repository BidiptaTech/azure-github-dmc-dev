import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from "js-cookie";

import { BASE_URL } from '@/services/api';

// Async thunk for fetching enquiries
export const fetchEnquiries = createAsyncThunk(
  'enquiryList/fetchEnquiries',
  async (agentId =null, { getState, rejectWithValue }) => {
    try {
    //   const { token } = getState().auth;
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
        params: agentId ? { 'agent_id': agentId } : {}
      });
      
      if (response.data.success) {
        return response.data.enquiries;
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
  status: 'idle', // 'idle' | 'loading' | 'succeeded' | 'failed'
  error: null,
  totalCount: 0,
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
  const enquiries = action.payload || [];

  state.status = 'succeeded';
  state.enquiries = enquiries;
  state.totalCount = enquiries.length;

  // Calculate stats safely
  state.stats.total = enquiries.length;
  state.stats.withHotel = enquiries.filter(e => e.hotel).length;
  state.stats.withPickup = enquiries.filter(e => e.local_transfer).length;
  state.stats.withPort = enquiries.filter(e => e.port).length;
  state.stats.withAttractions = enquiries.filter(e => e.attraction).length;
  state.stats.withPackagedAttractions = enquiries.filter(e => e.packaged_attractions).length;
  state.stats.withRestaurants = enquiries.filter(e => e.restaurant).length;
  state.stats.withGuides = enquiries.filter(e => e.guide).length;
})
      .addCase(fetchEnquiries.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.payload || 'Unknown error occurred';
      });
  },
});

export default enquiryListSlice.reducer; 
import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from "js-cookie";

import { BASE_URL } from '@/services/api';


export const convertEnquiresToTourId = createAsyncThunk(
  'convertToTour/sendEnquiryId',
  async ({ agentId = null, enquiryID = null }, { getState, rejectWithValue }) => {
    try {
    //   const { token } = getState().auth;
      const token = Cookies.get("authToken");
      
      if (!token) {
        return rejectWithValue('Authentication token not found');
      }
       const params = {};
      if (agentId) params.agent_id = agentId;
      if (enquiryID) params.enquiry_id = enquiryID;
      const response = await axios.post(`${BASE_URL}/create-enquiry-tour`,{},{
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
         params
      });
      
      if (response.data.success) {
        console.log(response.data.success);
        return response.data.data;
        
      } else {
        return rejectWithValue(response.data.message || 'Failed to fetch enquiries');
      }
    } catch (error) {
      return rejectWithValue(error.message || 'An error occurred while fetching data');
    }
  }
);


const initialState = {
  message: null,     // stores the "Enquiry successfully converted to tour"
  loading: false,
  error: null,
};

const convertToTourSlice = createSlice({
  name: 'convertToTour',
  initialState,
  reducers: {
    resetConvertState: (state) => {
      state.message = null;
      state.loading = false;
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(convertEnquiresToTourId.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(convertEnquiresToTourId.fulfilled, (state, action) => {
        state.loading = false;
        state.message = action.payload;
      })
      .addCase(convertEnquiresToTourId.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload || 'Failed to convert enquiry';
      });
  },
});

export const { resetConvertState } = convertToTourSlice.actions;
export default convertToTourSlice.reducer;
import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from "js-cookie";
import { logoutUser } from './authSlices';

import { BASE_URL } from '@/services/api';

export const fetchAgencies= createAsyncThunk(
  'agentList/fetchAgencies',
  async (data, { getState, rejectWithValue }) => {
    try {
    //   const { token } = getState().auth;
      const token = Cookies.get("authToken");
      const {dmcId, country} = data;
      
      if (!token) {
        return rejectWithValue('Authentication token not found');
      }
      
      const response = await axios.get(`${BASE_URL}/agencies-list`,{
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        params: {
          dmc_id: dmcId,
          country: country
        }
      });
      
      if (response.data.success) {
        // console.log(response.data.success);
        return response.data.agencies;
        
      } else {
        return rejectWithValue(response.data.message || 'Failed to fetch agencies');
      }
    } catch (error) {
      return rejectWithValue(error.message || 'An error occurred while fetching data');
    }
  }
);

export const fetchAgentList = createAsyncThunk(
  'agentList/fetchAgentList',
  async (_, { getState, rejectWithValue }) => {
    try {
    //   const { token } = getState().auth;
      const token = Cookies.get("authToken");
      
      if (!token) {
        return rejectWithValue('Authentication token not found');
      }
      
      const response = await axios.get(`${BASE_URL}/agents-list`,{
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
          "Accept": "application/json"
        }
      });
      
      if (response.data.success) {
        // console.log(response.data.success);
        return response.data.agents;
        
      } else {
        return rejectWithValue(response.data.message || 'Failed to fetch enquiries');
      }
    } catch (error) {
      return rejectWithValue(error.message || 'An error occurred while fetching data');
    }
  }
);

export const fetchAgentListByAgency = createAsyncThunk(
  'agentList/fetchAgentListByAgency',
  async (agencyId, { getState, rejectWithValue }) => {
    try {
      const token = Cookies.get("authToken");
      
      if (!token) {
        return rejectWithValue('Authentication token not found');
      }
      
      const response = await axios.get(`${BASE_URL}/agents-list`,{
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        params: {
          agency_id: agencyId
        }
      });
      
      if (response.data.success) {
        return response.data.agents;
      } else {
        return rejectWithValue(response.data.message || 'Failed to fetch agents');
      }
    } catch (error) {
      return rejectWithValue(error.message || 'An error occurred while fetching data');
    }
  }
);

const initialState = {
  agents: [],
  agencies: [],
  selectedAgency: null,
  loading: false,
  agenciesLoading: false,
  error: null,
};
const agentListSlice =createSlice({
    name:"agentList",
    initialState,
    reducers:{
        resetAgentList :(state)=>{
            state.agents=[];
            state.loading =false;
            state.error=null
        },
        setSelectedAgency: (state, action) => {
            state.selectedAgency = action.payload;
        },
        resetAgencies: (state) => {
            state.agencies = [];
            state.selectedAgency = null;
            state.agenciesLoading = false;
        }
    },
    extraReducers:(builder) =>{
        builder
        // Fetch Agent List
        .addCase(fetchAgentList.pending,(state)=>{
            state.loading =true;
            state.error=null;
        })
        .addCase(fetchAgentList.fulfilled,(state,action)=>{
            state.loading =false;
            state.agents=action.payload;
        })
        .addCase(fetchAgentList.rejected,(state,action)=>{
            state.loading =false;
            state.error =action.payload ||"Failed to fetch agent list";
        })
<<<<<<< HEAD
        .addCase(logoutUser.fulfilled, (state) => {
            // Clear agent list when user logs out successfully
            state.agents = [];
            state.loading = false;
            state.error = null;
        })
        .addCase(logoutUser.rejected, (state) => {
            // Clear agent list even if logout fails
            state.agents = [];
            state.loading = false;
            state.error = null;
        })
=======
        // Fetch Agencies
        .addCase(fetchAgencies.pending,(state)=>{
            state.agenciesLoading =true;
            state.error=null;
        })
        .addCase(fetchAgencies.fulfilled,(state,action)=>{
            state.agenciesLoading =false;
            state.agencies=action.payload;
        })
        .addCase(fetchAgencies.rejected,(state,action)=>{
            state.agenciesLoading =false;
            state.error =action.payload ||"Failed to fetch agencies";
        })
        // Fetch Agent List by Agency
        .addCase(fetchAgentListByAgency.pending,(state)=>{
            state.loading =true;
            state.error=null;
        })
        .addCase(fetchAgentListByAgency.fulfilled,(state,action)=>{
            state.loading =false;
            state.agents=action.payload;
        })
        .addCase(fetchAgentListByAgency.rejected,(state,action)=>{
            state.loading =false;
            state.error =action.payload ||"Failed to fetch agents by agency";
        });
>>>>>>> 0e166f5fd0d61e66c6a2d2efce741807f2ffd463
    }


})
export const {resetAgentList, setSelectedAgency, resetAgencies} = agentListSlice.actions
export default agentListSlice.reducer;
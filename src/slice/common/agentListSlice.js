import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import Cookies from "js-cookie";

import { BASE_URL } from '@/services/api';


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
        console.log(response.data.success);
        return response.data.agents;
        
      } else {
        return rejectWithValue(response.data.message || 'Failed to fetch enquiries');
      }
    } catch (error) {
      return rejectWithValue(error.message || 'An error occurred while fetching data');
    }
  }
);

const initialState = {
  agents: [],
  loading: false,
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

        } ,      
    },
    extraReducers:(builder) =>{
        builder.addCase(fetchAgentList.pending,(state)=>{
            state.loading =true,
            state.error=null

        })
        .addCase(fetchAgentList.fulfilled,(state,action)=>{
            state.loading =false;
            state.agents=action.payload
        })
        .addCase(fetchAgentList.rejected,(state,action)=>{
            state.loading =false;
            state.error =action.payload ||"Failed to fetch agent list"

        })
    }


})
export const {resetAgentList} =agentListSlice.actions
export default agentListSlice.reducer;
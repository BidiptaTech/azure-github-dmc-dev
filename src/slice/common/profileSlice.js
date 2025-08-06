import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import { BASE_URL } from '@/services/api';
import Cookies from 'js-cookie';

// Async thunk for updating profile
// Expected fields: old_password, new_password, confirm_password, image, phone_no, agent_address
// API fields: old_password, new_password, confirm_password, image, phone, agent_address
export const updateProfile = createAsyncThunk(
  'profile/updateProfile',
  async (profileData, { rejectWithValue }) => {
    try {
      console.log('Profile data being sent:', profileData);
      const formData = new FormData();
      if (profileData.old_password) formData.append('old_password', profileData.old_password);
      if (profileData.new_password) formData.append('new_password', profileData.new_password);
      if (profileData.confirm_password) formData.append('confirm_password', profileData.confirm_password);
      if (profileData.image) {
        console.log('Appending image to FormData as image:', profileData.image);
        formData.append('image', profileData.image);
      }
      if (profileData.phone_no) formData.append('phone', profileData.phone_no);
      if (profileData.agent_address) formData.append('agent_address', profileData.agent_address);
      
      // Debug: Log what's being sent to the API
      // console.log('FormData contents:');
      for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
      }

      const authToken = Cookies.get('authToken');
      const AgentId = Cookies.get('AgentId');
      const headers = {};
      if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
      if (AgentId) headers['agent-id'] = AgentId;

      const response = await axios.post(
        `${BASE_URL}/update-profile`,
        formData,
        { headers }
      );
      // console.log('API response:', response.data);
      return response.data;
    } catch (error) {
      console.error('Profile update error:', error);
      console.error('Error response:', error.response?.data);
      return rejectWithValue(
        error.response && error.response.data ? error.response.data : error.message
      );
    }
  }
);

const profileSlice = createSlice({
  name: 'profile',
  initialState: {
    loading: false,
    success: false,
    error: null,
    data: null,
  },
  reducers: {
    resetProfileState: (state) => {
      state.loading = false;
      state.success = false;
      state.error = null;
      state.data = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(updateProfile.pending, (state) => {
        state.loading = true;
        state.success = false;
        state.error = null;
      })
      .addCase(updateProfile.fulfilled, (state, action) => {
        state.loading = false;
        state.success = true;
        state.data = action.payload;
      })
      .addCase(updateProfile.rejected, (state, action) => {
        state.loading = false;
        state.success = false;
        state.error = action.payload || 'Something went wrong';
      });
  },
});

export const { resetProfileState } = profileSlice.actions;
export default profileSlice.reducer; 
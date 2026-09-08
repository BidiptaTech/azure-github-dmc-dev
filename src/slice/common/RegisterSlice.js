import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';
import { BASE_URL } from '@/services/api';
import Cookies from 'js-cookie';

export const registerSlice = createAsyncThunk(
    'register/register',
    async (registerData, { rejectWithValue }) => {
        try {
            // Create FormData for file upload
            const formData = new FormData();
            
            // Add text fields
            formData.append('company_name', registerData.company_name);
            formData.append('salutation', registerData.salutation);
            formData.append('name', registerData.name);
            formData.append('email', registerData.email);
            formData.append('user_country', registerData.user_country);
            formData.append('city', registerData.city);
            formData.append('agent_address', registerData.agent_address);
            formData.append('code', registerData.code);
            formData.append('phone', registerData.phone);
            formData.append('id_card', registerData.id_card);
            formData.append('card_number', registerData.card_number);
            formData.append('password', registerData.password);
            
            // Add arrays as JSON strings
            if (registerData.country && Array.isArray(registerData.country)) {
                formData.append('country', JSON.stringify(registerData.country));
            }
            
            // Add files with debugging
            if (registerData.agent_image && registerData.agent_image instanceof File) {
                console.log('Adding agent_image file:', {
                    name: registerData.agent_image.name,
                    size: registerData.agent_image.size,
                    type: registerData.agent_image.type
                });
                formData.append('agent_image', registerData.agent_image);
            } else {
                console.warn('agent_image is not a valid File object:', registerData.agent_image);
            }
            
            if (registerData.image && registerData.image instanceof File) {
                console.log('Adding image file:', {
                    name: registerData.image.name,
                    size: registerData.image.size,
                    type: registerData.image.type
                });
                formData.append('image', registerData.image);
            } else {
                console.warn('image is not a valid File object:', registerData.image);
            }
            
            // Debug: Log FormData entries (excluding files for readability)
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                if (key === 'agent_image' || key === 'image') {
                    console.log(`${key}:`, value instanceof File ? `File: ${value.name} (${value.size} bytes)` : value);
                } else {
                    console.log(`${key}:`, value);
                }
            }
            
            // Set the correct content type for multipart/form-data
            const config = {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            };
            
            console.log('Sending request to:', `${BASE_URL}/register-agent`);
            const response = await axios.post(`${BASE_URL}/register-agent`, formData, config);
            console.log('API Response:', response.data);
            return response.data;
        } catch (error) {
            console.error('Registration API Error:', error);
            console.error('Error Response:', error.response?.data);
            return rejectWithValue(error.response?.data || error.message);
        }
    }
)

export const sendOTPSlice = createAsyncThunk(
    'register/sendOTP',
    async (sendOTPData, { rejectWithValue }) => {
      try {
        console.log("sendOTPData", sendOTPData);
  
        // Convert data to FormData
        const formData = new FormData();
        for (const key in sendOTPData) {
          const value = sendOTPData[key];
  
          // Handle arrays (like 'country') properly
          if (Array.isArray(value)) {
            value.forEach((item, index) => {
              formData.append(`${key}[${index}]`, item);
            });
          }
          // Handle File objects
          else if (value instanceof File) {
            formData.append(key, value);
          }
          // All other values
          else {
            formData.append(key, value);
          }
        }
  
        // Send as multipart/form-data
        const response = await axios.post(`${BASE_URL}/send-otp`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
  
        return response.data;
      } catch (error) {
        console.error('Send OTP API Error:', error);
        return rejectWithValue(error.response?.data || error.message);
      }
    }
  );
  

export const verifyOTPSlice = createAsyncThunk(
    'register/verifyOTP',
    async (verifyOTPData, { rejectWithValue }) => {
        try {
            const response = await axios.post(`${BASE_URL}/verify-otp`, verifyOTPData);
            return response.data;
        } catch (error) {
            console.error('Verify OTP API Error:', error);
            return rejectWithValue(error.response?.data || error.message);
        }
    }
)

const RegisterSlice = createSlice({
    name: 'register',
    initialState: {
        loading: false,
        success: false,
        error: null,
        data: null,
    },
    reducers: {
        resetRegisterState: (state) => {
            state.loading = false;
            state.success = false;
            state.error = null;
            state.data = null;
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(registerSlice.pending, (state) => {
                state.loading = true;
            })
            .addCase(registerSlice.fulfilled, (state, action) => {
                state.loading = false;
                state.success = true;
                state.data = action.payload;
            })
            .addCase(registerSlice.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            })
    }
})

export const { resetRegisterState } = RegisterSlice.actions;
export default RegisterSlice.reducer;
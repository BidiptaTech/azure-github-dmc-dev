import { createSlice } from '@reduxjs/toolkit';

// Initial state with additional fields
const initialState = {
  policies: [],
  checkInTime: '12 PM',
  checkOutTime: '11 AM',
  propertyName: '',
  propertyId: '',
  // Add any other dynamic data related to the property
};

// Create slice
const policiesSlice = createSlice({
  name: 'policies',
  initialState,
  reducers: {
    setPropertyDetails(state, action) {
      // This action sets all property details at once
      const { policies, checkInTime, checkOutTime, propertyName, propertyId } = action.payload;
      state.policies = policies || state.policies; // Fallback to previous state if undefined
      state.checkInTime = checkInTime || state.checkInTime;
      state.checkOutTime = checkOutTime || state.checkOutTime;
      state.propertyName = propertyName || state.propertyName;
      state.propertyId = propertyId || state.propertyId;
    },
    setPolicies(state, action) {
      state.policies = action.payload;
    },
    setCheckInTime(state, action) {
      state.checkInTime = action.payload;
    },
    setCheckOutTime(state, action) {
      state.checkOutTime = action.payload;
    },
    setPropertyName(state, action) {
      state.propertyName = action.payload;
    },
    setPropertyId(state, action) {
      state.propertyId = action.payload;
    },
  },
});

// Export actions
export const { 
  setPropertyDetails, 
  setPolicies, 
  setCheckInTime, 
  setCheckOutTime, 
  setPropertyName, 
  setPropertyId 
} = policiesSlice.actions;

// Export the reducer
export default policiesSlice.reducer;

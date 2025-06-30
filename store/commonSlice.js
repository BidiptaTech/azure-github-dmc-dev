import { createSlice } from '@reduxjs/toolkit';

const commonSlice = createSlice({
  name: 'common',
  initialState: {
    bookingMode: 'normal',
    // ... other state properties
  },
  reducers: {
    setBookingMode: (state, action) => {
      state.bookingMode = action.payload;
    },
    // ... other reducers
  },
});

export const { setBookingMode } = commonSlice.actions;
export default commonSlice.reducer; 
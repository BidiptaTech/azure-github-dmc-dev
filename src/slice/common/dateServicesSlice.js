import { createSlice } from "@reduxjs/toolkit";

const dateServiceSlice = createSlice({
  name: "dateService",
  initialState: {
    services: [], // Stores booking data
  },
  reducers: {
    // Action to set the date service data
    setDateService: (state, action) => {
      console.log("Setting date service data:", action.payload);
      state.services = action.payload; // Update the state with the new data
      console.log("date service data:", state.services);
    },
  },
});

export const { setDateService } = dateServiceSlice.actions; // Export the action
export default dateServiceSlice.reducer;

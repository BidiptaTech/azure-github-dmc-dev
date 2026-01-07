import { createSlice } from "@reduxjs/toolkit";

const citySlice = createSlice({
  name: "city",
  initialState: {
    city: [],
  },
  reducers: {
    setCity: (state, action) => {
      console.log("City Data being set srk:", action.payload);
    state.city = action.payload;
    },
  },
});

export const { setCity } = citySlice.actions;
export default citySlice.reducer;

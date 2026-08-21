import { createSlice } from "@reduxjs/toolkit";
import { normalizeCityList } from "@/utils/locationFormat";

const citySlice = createSlice({
  name: "city",
  initialState: {
    city: [],
  },
  reducers: {
    setCity: (state, action) => {
      // Always store "City, Country" style strings when possible
      const payload = action.payload;
      if (
        payload &&
        typeof payload === "object" &&
        !Array.isArray(payload) &&
        Array.isArray(payload.cities)
      ) {
        state.city = normalizeCityList(payload.cities, payload.country || "");
      } else {
        state.city = normalizeCityList(payload);
      }
    },
  },
});

export const { setCity } = citySlice.actions;
export default citySlice.reducer;

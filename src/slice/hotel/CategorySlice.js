import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  sortOption: "",
  payAtHotel: false,
  topBrands: false,
  budgetProperties: false,
  userRating: false,
  price1: false,
  price2: false,
  price3: false,
  star1: false, // 3 Star
  star2: false, // 4 Star
  star3: false, // 5 Star
  priceMode: ["dmc", "travclick"], // Default to show both DMC and travclick prices
  priceRange: { min: 0, max: 1000 }, // Default price range before API data loads
  priceBounds: { min: 0, max: 1000 }, // Store the calculated min and max from API data
  priceModeId: "",  
};

const CategorySlice = createSlice({
  name: "category",
  initialState,
  reducers: {
    setSortOption: (state, action) => {
      state.sortOption = action.payload; // Sets sort option directly
    },
    setPriceMode: (state, action) => {
      // Update this to handle both string or array formats consistently
      // Always store priceMode as the original value passed
      state.priceMode = action.payload;
    },
    setPriceModeId: (state, action) => {
      state.priceModeId = action.payload; // Sets the price mode ID
    },
    setPriceBounds: (state, action) => {
      // Set the min and max price bounds from API data
      if (typeof action.payload === 'object' && 'min' in action.payload && 'max' in action.payload) {
        const min = parseFloat(action.payload.min) || 0;
        const max = parseFloat(action.payload.max) || 1000;
        state.priceBounds = { 
          min: isFinite(min) ? min : 0, 
          max: isFinite(max) ? max : 1000 
        };
      }
    },
    setFilter: (state, action) => {
      // Handle each filter type appropriately
      const { filterName, value } = action.payload;
      
      // Special handling for priceRange
      if (filterName === "priceRange") {
        // If array is passed, convert to object format
        if (Array.isArray(value) && value.length === 2) {
          // Ensure values are valid numbers
          const min = parseFloat(value[0]) || 0;
          const max = parseFloat(value[1]) || 1000;
          state.priceRange = { 
            min: isFinite(min) ? min : 0, 
            max: isFinite(max) ? max : 1000 
          };
        } 
        // If object is passed, use directly but validate values
        else if (typeof value === 'object' && 'min' in value && 'max' in value) {
          const min = parseFloat(value.min) || 0;
          const max = parseFloat(value.max) || 1000;
          state.priceRange = { 
            min: isFinite(min) ? min : 0, 
            max: isFinite(max) ? max : 1000 
          };
        }
      } else {
        // For boolean filters
        state[filterName] = value;
      }
    },
    clearFilters: (state) => {
      // Reset to initial values, but preserve sortOption, priceMode, and priceBounds
      const tempSortOption = state.sortOption;
      const tempPriceMode = state.priceMode;
      const tempPriceBounds = state.priceBounds;
      
      // Reset all filters to initial state
      Object.assign(state, initialState);
      
      // Restore preserved values
      state.sortOption = tempSortOption;
      state.priceMode = tempPriceMode;
      state.priceBounds = tempPriceBounds;
    },
  },
});

export const { 
  setSortOption, 
  setPriceMode, 
  setPriceModeId, 
  setFilter, 
  setPriceBounds, 
  clearFilters 
} = CategorySlice.actions;
export default CategorySlice.reducer;

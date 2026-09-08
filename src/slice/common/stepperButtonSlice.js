import { createSlice } from "@reduxjs/toolkit";

const stepperButtonSlice = createSlice({
  name: "stepperButton",
  initialState: {
    // Track booking responses for each service
    serviceResponses: {
      hotel: null,
      port: null,
      attraction: null,
      guide: null,
      restaurent: null,
      travel: null,
    },
    // Track which buttons should be shown for each step
    buttonVisibility: {
      hotel: { showSkip: true, showNext: false },
      port: { showSkip: true, showNext: false },
      attraction: { showSkip: true, showNext: false },
      guide: { showSkip: true, showNext: false },
      restaurent: { showSkip: true, showNext: false },
      travel: { showSkip: true, showNext: false },
    },
  },
  reducers: {
    // Update booking response for a specific service
    updateServiceResponse: (state, action) => {
      const { service, response } = action.payload;
      state.serviceResponses[service] = response;
      
      // Update button visibility based on response
      if (response && response.success !== false) {
        // If we have a successful response, show Next button, hide Skip
        state.buttonVisibility[service] = { showSkip: false, showNext: true };
      } else {
        // If no response or failed response, show Skip button, hide Next
        state.buttonVisibility[service] = { showSkip: true, showNext: false };
      }
    },
    
    // Reset service response
    resetServiceResponse: (state, action) => {
      const { service } = action.payload;
      state.serviceResponses[service] = null;
      state.buttonVisibility[service] = { showSkip: true, showNext: false };
    },
    
    // Reset all service responses
    resetAllServiceResponses: (state) => {
      Object.keys(state.serviceResponses).forEach(service => {
        state.serviceResponses[service] = null;
        state.buttonVisibility[service] = { showSkip: true, showNext: false };
      });
    },
    
    // Manually set button visibility for a service
    setButtonVisibility: (state, action) => {
      const { service, showSkip, showNext } = action.payload;
      state.buttonVisibility[service] = { showSkip, showNext };
    },
  },
});

export const {
  updateServiceResponse,
  resetServiceResponse,
  resetAllServiceResponses,
  setButtonVisibility,
} = stepperButtonSlice.actions;

export default stepperButtonSlice.reducer; 
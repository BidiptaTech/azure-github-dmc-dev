//import { Details } from "@mui/icons-material";
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { logoutUser } from "../common/authSlices";
import { BASE_URL } from "@/services/api";
import { selectDmcId } from "../dmc/dmcSlice";
import { updateServiceResponse } from "../common/stepperButtonSlice";
import { setHaveBooking } from "../common/commonSlice";


export const fetchVehicles = createAsyncThunk(
  "pickupDrop/fetchVehicles",
  async (params1 = {}, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state
      const { 
        PickupPlaceid, 
        DropoffPlaceid, 
        pickupdate, 
        entrytime, 
        PickupPlaceid1, 
        DropoffPlaceid1, 
        selectionType 
      } = state.pickupDrop;
      
      

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // ✅ Determine which date to use
      const travelDate = JSON.stringify({ 0: pickupdate });
      
      const selectedDmcId = selectDmcId(state);
      // ✅ Build query parameters dynamically
      let params;
      
      // Ensure we're using the correct selection type
      if (selectionType === "Entry Port") {
       
        if (!PickupPlaceid) {
         
          throw new Error("Pickup location is required");
        }
        
        params = {
          pickup: JSON.stringify(PickupPlaceid),
          date: travelDate, // ✅ Include date
          time: JSON.stringify(entrytime),
          dmc_id: selectedDmcId,
          start: (params1 && params1.start) || undefined,
          limit: (params1 && params1.limit) || undefined,
        };
        
        if (DropoffPlaceid) {
          params.dropoff = JSON.stringify(DropoffPlaceid); // ✅ Add dropoff only if available
        }
      }
      else if (selectionType === "Exit Port") {
        
        // No need to parse JSON string as it's already an object
        const pickupCoords = PickupPlaceid1;
        
        if (!pickupCoords) {
         
          throw new Error("Pickup location is required for Exit Port");
        }
        
       
        
        params = {
          pickup: JSON.stringify(pickupCoords),
          date: travelDate, // ✅ Include date
          time: JSON.stringify(entrytime),
          dmc_id: selectedDmcId,
          start: (params1 && params1.start) || undefined,
          limit: (params1 && params1.limit) || undefined,
        };
        
        if (DropoffPlaceid1) {
          // No need to parse JSON string as it's already an object
          const dropoffCoords = DropoffPlaceid1;
          params.dropoff = JSON.stringify(dropoffCoords); // ✅ Add dropoff only if available
          
        }
      } else {
        
        throw new Error(`Invalid selection type: ${selectionType}`);
      }
      
      // Log the final params and selection type before making the API call
     
      
      // ✅ Make API request with dynamic params
     
      const response = await axios.get(`${BASE_URL}/vehicles-list`, {
        params, // Axios automatically adds available parameters
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

      
      return response.data;
    } catch (error) {
      
      if (error.response?.status === 401) {
       
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const fetchVehicleDetails = createAsyncThunk(
  "pickupDrop/fetchVehiclesDetails",
  async ({ city, country, type }, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state

      const {
        PickupPlaceid,
        DropoffPlaceid,
        selectedVehicle,
        selectedVehicle1,
        pickupdate,
        entrytime,
        picktype,
        droptype,
        selectionType,
        PickupPlaceid1,
        DropoffPlaceid1,
      } = state.pickupDrop;

      
      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;
      const selectedDmcId = selectDmcId(state);
      // Corrected conditional statement
      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)" ||
        userRole === "DMC Assistant Operational Manager" ||
        userRole === "DMC Operational Manager" ||
        userRole === "Operational Head(DMC)"
      ) {
        AgentId = agentID;
      } else {
        AgentId = Cookies.get("AgentId");
      }

      // ✅ Determine which date to use
      const travelDate = JSON.stringify({ 0: pickupdate });

      

      // ✅ Build query parameters dynamically
      const params =
        type === "zone"
          ? {
            ...(selectionType === "Entry Port" && {
              from_zone_id: PickupPlaceid,
              to_zone_id: DropoffPlaceid,
              vehicle_id: selectedVehicle?.id,
              date: travelDate,
              time: JSON.stringify(entrytime),
              mode: selectedVehicle?.mode,
              dmc_id: selectedDmcId,
              pickup_type: picktype,
              drop_type: droptype,
              city: city,
              country: country,
              type,
            }),
            ...(selectionType === "Exit Port" && {
              from_zone_id: PickupPlaceid1,
              to_zone_id: DropoffPlaceid1,
              vehicle_id: selectedVehicle1?.id, // ✅ Correct way to access id
              mode: selectedVehicle1?.mode, // ✅ Correct way to access mode
              dmc_id: selectedDmcId, // ✅ Correct way to access dmcId
              pickup_type: picktype,
              drop_type: droptype,
              date: travelDate,
              time: JSON.stringify(entrytime),
              city: city,
              country: country,
              type,
            }),
          } : {
              ...(selectionType === "Entry Port" && {
              pickup: JSON.stringify(PickupPlaceid),
              ...(DropoffPlaceid && {
                dropoff: JSON.stringify(DropoffPlaceid),
              }), // ✅ Add dropoff if available
              date: travelDate, // ✅ Include date
              time: JSON.stringify(entrytime),
              vehicle_id: selectedVehicle?.id, // ✅ Correct way to access id
              mode: selectedVehicle?.mode, // ✅ Correct way to access mode
              dmc_id: selectedDmcId, // ✅ Correct way to access dmcId
              city: city,
              country: country,
            }),
            ...(selectionType === "Exit Port" && {
              pickup: JSON.stringify(PickupPlaceid1),
              ...(DropoffPlaceid1 && {
                dropoff: JSON.stringify(DropoffPlaceid1),
              }),
              vehicle_id: selectedVehicle1?.id, // ✅ Correct way to access id
              mode: selectedVehicle1?.mode, // ✅ Correct way to access mode
              dmc_id: selectedDmcId, // ✅ Correct way to access dmcId
              date: travelDate,
              time: JSON.stringify(entrytime),
              city: city,
              country: country,
            }),
            };

      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/vehicle-details`, {
        params, // Axios automatically adds available parameters
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const fetchPortCity = createAsyncThunk(
  "pickupDrop/fetchPortCity",
  async ({ city, tourId, type }, { rejectWithValue, dispatch }) => {
    try {
     
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
       
        throw new Error("Authorization required");
      }

      // Ensure tourId is properly formatted
      if (tourId === undefined || tourId === null || tourId === "") {
       
        throw new Error("Tour ID is required");
      }

      // Ensure tourId is a number for the API
      const numericTourId =
        typeof tourId === "string" ? parseInt(tourId, 10) : tourId;

      if (isNaN(numericTourId)) {
       
        throw new Error("Tour ID must be a valid number");
      }

      // ✅ Build query parameters dynamically
      const params = {
        city: city,
        tour_id: numericTourId, // Ensure it's a number
        type: type,
      };

    

      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/get-ports`, {
        params, // Axios automatically adds available parameters
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
          "Content-Type": "application/json",
        },
      });

     
      return response.data;
    } catch (error) {
     

      if (error.response) {
       
      }

      if (error.response?.status === 401) {
       
        await dispatch(logoutUser()); // Ensure the logout process completes
      }

      return rejectWithValue(error.response?.data || error.message);
    }
  }
);
export const fetchLocalZone = createAsyncThunk(
  "pickupDrop/fetchLocalZone",
  async ({ id, type }, { rejectWithValue, dispatch, getState }) => {
    const state = getState();
    try {
      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;

      // Corrected conditional statement
      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)" ||
        userRole === "DMC Assistant Operational Manager" ||
        userRole === "DMC Operational Manager" ||
        userRole === "Operational Head(DMC)"
      ) {
        AgentId = agentID;
      } else {
        AgentId = Cookies.get("AgentId");
      }

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const response = await axios.get(`${BASE_URL}/zone-lists`, {
        params: {
          id,
          type,
        },
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

     

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        
        dispatch(logoutUser());
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const fetchZoneVehicles = createAsyncThunk(
  "pickupDrop/fetchZoneVehicles",
  async (params1 = {}, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state
      const {
        PickupPlaceid,
        DropoffPlaceid,
        PickupPlaceid1,
        DropoffPlaceid1,
        entrypickup,
        exitpickup,
        entrydropoff,
        exitdropoff,
        pickupdate,
        entrytime,
        exittime,
        picktype,
        droptype,
        selectionType,
      } = state.pickupDrop;

    

      // Check for pickup location based on selection type
      if (selectionType === "Entry Port") {
        if (!PickupPlaceid) {
          throw new Error("Pickup location is required");
        }
      } else if (selectionType === "Exit Port") {
        if (!PickupPlaceid1) {
          throw new Error("Pickup location is required");
        }
      } else {
        // Fallback check for both if selectionType is not properly set
        if (!PickupPlaceid && !PickupPlaceid1) {
          throw new Error("Pickup location is required");
        }
      }

      if (!droptype) {
        console.error("Drop-off type is missing");
      }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
      const selectedDmcId = selectDmcId(state);
     
      // ✅ Format pickdate as JSON { "0": "YYYY-MM-DD" }
      const travelDate = JSON.stringify({ 0: pickupdate });

      // ✅ Build query parameters dynamically
      const params =
        selectionType === "Entry Port"
          ? {
              pickupid: PickupPlaceid,
              dropoffid: DropoffPlaceid,
              PickUpLocation: entrypickup,
              DropOffLocation: entrydropoff,
              time: entrytime,
              date: travelDate, // ✅ Include formatted date
              pickup_type: picktype,
              drop_type: droptype, // Set default to 'hotel' if droptype is missing
              dmc_id: selectedDmcId,
              start: (params1 && params1.start) || undefined,
              limit: (params1 && params1.limit) || undefined,
            }
          : {
              pickupid: PickupPlaceid1,
              dropoffid: DropoffPlaceid1,
              PickUpLocation: exitpickup,
              DropOffLocation: exitdropoff,
              time: exittime,
              date: travelDate, // ✅ Include formatted date
              pickup_type: picktype,
              drop_type: droptype, // Set default to 'hotel' if droptype is missing
              dmc_id: selectedDmcId,
              start: (params1 && params1.start) || undefined,
              limit: (params1 && params1.limit) || undefined,
            };

      
      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/zone-vehicles`, {
        params,
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
      });

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Async thunk for fetching hotels
export const submitPickupDrop = createAsyncThunk(
  "pickupDrop/submit",
  async ({ selectedType }, { getState, rejectWithValue, dispatch }) => {
    try {
      const state = getState();
      const {
        entrypickup,
        entrydropoff,
        exitpickup,
        exitdropoff,
        pickupdate,
        //exitpickupdate,
        entrytime,
        exittime,
        adult,
        bookingtype,
        children,

        type,
        type1,
        tourid,
        details,
        details1,
      } = state.pickupDrop;

      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;
      const selectedDmcId = selectDmcId(state);
      // Corrected conditional statement
      let AgentId;
      if (
        userRole === "Sales Head(DMC)" ||
        userRole === "Sales Manager (DMC)" ||
        userRole === "Assistant Manager (DMC)"
      ) {
        AgentId = agentID;
      } else {
        AgentId = Cookies.get("AgentId");
      }

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

    
      let formData = {};
      let formData1 = {};
      
      

      if (
        entrypickup &&
        entrydropoff &&
        pickupdate &&
        entrytime &&
        adult !== null &&
        adult !== undefined &&
        children !== null &&
        children !== undefined
      ) {
        
        formData = {
          data: details.map(item => ({ ...item, dmc_id: selectedDmcId })),
          type: type,
          agent_id: AgentId,
          tour_id: tourid,
          bookingType: bookingtype,
        };
      }

      if (
        exitpickup &&
        exitdropoff &&
        pickupdate &&
        exittime &&
        adult !== null &&
        adult !== undefined &&
        children !== null &&
        children !== undefined
      ) {
       
        formData1 = {
          data: details1.map(item => ({ ...item, dmc_id: selectedDmcId })),
          type: type1,
          agent_id: AgentId,
          tour_id: tourid,
          bookingType: bookingtype,
        };
      }

      

      // Determine which FormData to send
      let selectedFormData = null;

      // Check if both formData and formData1 are available, prioritize formData1
      if (selectedType === "entry" && "data" in formData) {
        selectedFormData = formData;
      } else if (selectedType === "exit" && "data" in formData1) {
        selectedFormData = formData1;
      } else {
        throw new Error("No valid data to submit.");
      }
      

      
    

      // Actual API call
      const response = await axios.post(
        `${BASE_URL}/create-booking`,
        selectedFormData,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "multipart/form-data",
            "agent-id": AgentId,
          },
        }
      );

      

      // Check if response has the expected structure
      if (!response.data || !response.data.service) {
        console.error("Invalid response structure:", response.data);
        return response.data;
      }

      // Extracting response data - CORRECTED to use service.data
      const responseData = response.data.service.data || [];
      

      if (!Array.isArray(responseData)) {
        console.error(
          "Expected responseData to be an array but got:",
          responseData
        );

        return response.data;
      }

      // Look for specific identifiers in the data to determine port type
      const EntryPortData = responseData.filter((item) => {
        // Check if this is an entry port item by checking the response type or other properties
        return (
          // The type in the main response object
          response.data.service.type === "entry_port" ||
          // Or check if there's any explicit type in the item itself
          (item.type &&
            typeof item.type === "string" &&
            (item.type.includes("entry") || item.type === "entry_port"))
        );
      });

      const ExitPortData = responseData.filter((item) => {
        // Check if this is an exit port item
        return (
          // The type in the main response object
          response.data.service.type === "exit_port" ||
          // Or check if there's any explicit type in the item itself
          (item.type &&
            typeof item.type === "string" &&
            (item.type.includes("exit") || item.type === "exit_port"))
        );
      });

     

      // Since the API might return only one result based on the request type,
      // let's also consider the service type to determine what to dispatch
      if (
        response.data.service.type === "entry_port" &&
        responseData.length > 0
      ) {
        dispatch(setEntryport(responseData));
        
      } else if (
        response.data.service.type === "exit_port" &&
        responseData.length > 0
      ) {
        dispatch(setExitport(responseData));
        
      } else {
        // Apply the original filter logic as fallback
        if (EntryPortData.length > 0) {
          dispatch(setEntryport(EntryPortData));
        }

        if (ExitPortData.length > 0) {
          dispatch(setExitport(ExitPortData));
        }
      }

      // Check the type in the response and assign to formData or formData1
      const data = response.data.order.data;
     
      const responseType = response.data.order.type;

      

      if (responseType === "entry_port") {
        // Create an array to hold EntryPort data
        const formData = [];
        Object.entries(data).forEach(([key, value]) => {
          switch (key) {
            case "entrypickup":
              formData.push({ "Pick Up Location": value });
              break;
            case "entrydropoff":
              formData.push({ "Drop Off Location": value });
              break;
            case "pickupdate":
              formData.push({ Date: value });
              break;
            case "entrytime":
              formData.push({ Time: value });
              break;
            case "vehicles_name":
              formData.push({ Vehicle: value });
              break;
            case "adults":
              formData.push({ Adult: value });
              break;
            case "children":
              formData.push({ Children: value });
              break;
            default:
              break;
          }
        });
      

        // Update the state with formData
        dispatch(updateFormData(formData));
      } else if (responseType === "exit_port") {
        // Create an array to hold ExitPort data
        const formData1 = [];
        Object.entries(data).forEach(([key, value]) => {
          switch (key) {
            case "exitpickup":
              formData1.push({ "Pick Up Location": value });
              break;
            case "exitdropoff":
              formData1.push({ "Drop Off Location": value });
              break;
            case "exitpickupdate":
              formData1.push({ Date: value });
              break;
            case "exittime":
              formData1.push({ Time: value });
              break;
            case "vehicles_name":
              formData1.push({ Vehicle: value });
              break;
            case "adults":
              formData1.push({ Adult: value });
              break;
            case "children":
              formData1.push({ Children: value });
              break;
            default:
              break;
          }
        });
        

        // Update the state with formData1
        dispatch(updateFormData1(formData1));
      }

      // Update stepper button visibility based on booking response
      dispatch(updateServiceResponse({ 
        service: 'port', 
        response: response.data 
      }));

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        await dispatch(logoutUser()); // Ensure the logout process completes
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const pickupDropSlice = createSlice({
  name: "pickupDrop",
  initialState: {
    status: "idle", // Tracks the status: 'idle', 'loading', 'succeeded', 'failed'
    error: null, // Stores any errors from API requests
    entrypickup: "",
    entrydropoff: "",
    exitpickup: "",
    exitdropoff: "",
    pickupdate: "",
    exitpickupdate: "",
    entrytime: "",
    exittime: "",
    adult: "",
    vehicletype1: "",
    children: 0,
    traveller1: 0,
    formData: [], // Array to hold initial state variables for entry-related data
    formData1: [], // Array to hold initial state variables for exit-related data
    details: [],
    details1: [],
    type: "entry_port",
    type1: "exit_port",
    tourid: 0,
    entryport: [],
    exitport: [],
    vehicles: [],
    vehicles1: [],
    selectedVehicle: null,
    selectedVehicle1: null, // For Exit Port
    selectionType: "",
    PickupPlaceid: "",
    DropoffPlaceid: "",
    mode: {}, // Key-value pair: { vehicleId: mode }
    pricemode: "",
    bookingtype: "",
    response: [],
    selectedPort: "",
    // Add port city state
    portCityStatus: "idle",
    portCityError: null,
    portCityData: null,
    zone: [],
    picktype: "",
    droptype: "",
    portZoneType: "",
    PickupPlaceid1: "",
    DropoffPlaceid1: "",
    selectedVehicleId: null,
    adultCount: 1,
    childCount: 0,
  },
  reducers: {
    setentrypickup: (state, action) => {
      state.entrypickup = action.payload;
     
    },
    setentrydropoff: (state, action) => {
      state.entrydropoff = action.payload;
     
    },
    setexitpickup: (state, action) => {
      state.exitpickup = action.payload;
      
    },
    setexitdropoff: (state, action) => {
      state.exitdropoff = action.payload;
     
    },
    setPickupPlaceid: (state, action) => {
      state.PickupPlaceid = action.payload;
     
    },
    setDropoffPlaceid: (state, action) => {
      state.DropoffPlaceid = action.payload;
   
    },
    setPickupPlaceid1: (state, action) => {
      state.PickupPlaceid1 = action.payload;
   
    },
    setDropoffPlaceid1: (state, action) => {
      state.DropoffPlaceid1 = action.payload;
    
    },
    setpickupdate: (state, action) => {
      state.pickupdate = action.payload;
    },
    setexitpickupdate: (state, action) => {
      state.exitpickupdate = action.payload;
    },
    setentrytime: (state, action) => {
      state.entrytime = action.payload;
     
    },
    setexittime: (state, action) => {
      state.exittime = action.payload;
    },
    setadult: (state, action) => {
      state.adult = action.payload;
     
    },
    setchildren: (state, action) => {
      state.children = action.payload;
      
    },
    setvehicletype1: (state, action) => {
      state.vehicletype1 = action.payload;
    },
    settravellers1: (state, action) => {
      state.traveller1 = action.payload;
    },
    settourId: (state, action) => {
      state.tourid = action.payload;
    },
    // setbookingType: (state, action) => {
    //   state.type = action.payload;
    // },
    setbookingtype1: (state, action) => {
      state.bookingtype = action.payload;
      
    },
    setentrydata: (state, action) => {
      if (!Array.isArray(state.details)) {
        state.details = []; // Ensure details is an array
      }

      // If index 0 exists, merge the new data
      if (state.details[0]) {
        state.details[0] = { ...state.details[0], ...action.payload }; // ✅ Merge with existing data
      } else {
        state.details[0] = action.payload; // ✅ Insert if empty
      }

     
    },

    setexitdata: (state, action) => {
      if (!Array.isArray(state.details)) {
        state.details1 = []; // Ensure details is an array
      }
      if (state.details1[0]) {
        state.details1[0] = { ...state.details1[0], ...action.payload }; // Append new data instead of overriding
      } else {
        state.details1[0] = action.payload; // ✅ Insert if empty
      }
     
    },
    setResponse: (state, action) => {
      state.response = action.payload;
      
    },
    setPriceMode: (state, action) => {
      state.pricemode = action.payload;
      
    },
    setEntryport: (state, action) => {
      state.entryport = action.payload;
    },
    setExitport: (state, action) => {
      state.exitport = action.payload;
    },
    updateFormData: (state, action) => {
     
      state.formData = action.payload;
      
    },

    updateFormData1: (state, action) => {
      
      state.formData1 = action.payload;
      
    },
    setSelectedVehicle: (state, action) => {
      state.selectedVehicle = action.payload;
      
    },
    setSelectedVehicle1: (state, action) => {
      state.selectedVehicle1 = action.payload;
    },
    setSelectionType: (state, action) => {
      state.selectionType = action.payload;
      
    },
    setMode: (state, action) => {
      state.mode = { ...state.mode, ...action.payload }; // ✅ Merge new mode
      
    },
    setSelectedPort: (state, action) => {
      state.selectedPort = action.payload;
      
    },
    resetVehicles: (state, action) => {
      state.vehicles = [];
      state.vehicles1 = [];
      state.entrypickup = "";
      state.entrydropoff = "";
      state.exitpickup = "";
      state.exitdropoff = "";
      state.pickupdate = "";
      state.exitpickupdate = "";
      state.entrytime = "";
      state.DropoffPlaceid = "";
      state.PickupPlaceid = "";
      state.DropoffPlaceid1 = "";
      state.PickupPlaceid1 = "";
      state.error = null;
      //state.selectedVehicleId = null;
      state.status = "idle";
    },
    setPicktype: (state, action) => {
      state.picktype = action.payload;
     
    },
    setDroptype: (state, action) => {
      state.droptype = action.payload;
      
    },
    setPortZoneType: (state, action) => {
      state.portZoneType = action.payload;
     
    },
    setSelectedVehicleId: (state, action) => {
      state.selectedVehicleId = action.payload;
    },
    setAdultCount: (state, action) => {
      state.adultCount = action.payload;
    },
    setChildCount: (state, action) => {
      state.childCount = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchVehicles.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchVehicles.fulfilled, (state, action) => {
        state.status = "succeeded";
        // Check if response is an error message
        if (action.payload && typeof action.payload === 'object' && (action.payload.success === false)) {
          
          // Set empty array for vehicles based on selection type
          if(state.selectionType === "Entry Port"){
            state.vehicles = [];
            state.error = action.payload.message;
            
          }
          if(state.selectionType === "Exit Port"){
            state.vehicles1 = [];
            state.error = action.payload.message;
            
          }
        } else {
          // Normal success response - support infinite scrolling
          const { start = 0 } = action.meta?.arg || {};
          
          if(state.selectionType === "Entry Port"){
            if (start === 0) {
              // First page - replace vehicles
              state.vehicles = action.payload;
             
            } else {
              // Subsequent pages - accumulate vehicles
              const existingIds = new Set(state.vehicles.map(vehicle => vehicle.id));
              const newVehicles = action.payload.filter(vehicle => !existingIds.has(vehicle.id));
              state.vehicles = [...state.vehicles, ...newVehicles];
              
            }
          }
          if(state.selectionType === "Exit Port"){
            if (start === 0) {
              // First page - replace vehicles
              state.vehicles1 = action.payload;
              
            } else {
              // Subsequent pages - accumulate vehicles
              const existingIds = new Set(state.vehicles1.map(vehicle => vehicle.id));
              const newVehicles = action.payload.filter(vehicle => !existingIds.has(vehicle.id));
              state.vehicles1 = [...state.vehicles1, ...newVehicles];
             
            }
          }
        }
      })
      .addCase(fetchVehicles.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload.message; // Save the error in state
       
      })
      // Handle fetchPortCity actions
      .addCase(fetchPortCity.pending, (state) => {
        state.portCityStatus = "loading";
        state.portCityError = null;
      })
      .addCase(fetchPortCity.fulfilled, (state, action) => {
        state.portCityStatus = "succeeded";
        state.portCityData = action.payload;
       
      })
      .addCase(fetchPortCity.rejected, (state, action) => {
        state.portCityStatus = "failed";
        state.portCityError = action.payload;
        
      });

    builder
      .addCase(fetchZoneVehicles.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchZoneVehicles.fulfilled, (state, action) => {
        state.status = "succeeded";
        if(action.payload && typeof action.payload === 'object' && (action.payload.success === false)){
          
          if(state.selectionType === "Entry Port"){
            state.vehicles = [];
            state.error = action.payload.message;
            
          }
          if(state.selectionType === "Exit Port"){
            state.vehicles1 = [];
            state.error = action.payload.message;
           
          }
        } else {
          // Support infinite scrolling - check if it's initial load or subsequent load
          const { start = 0 } = action.meta?.arg || {};
          
          if(state.selectionType === "Entry Port"){
            if (start === 0) {
              // First page - replace vehicles
              state.vehicles = action.payload;
             
            } else {
              // Subsequent pages - accumulate vehicles
              const existingIds = new Set(state.vehicles.map(vehicle => vehicle.id));
              const newVehicles = action.payload.filter(vehicle => !existingIds.has(vehicle.id));
              state.vehicles = [...state.vehicles, ...newVehicles];
             
            }
          }
          if(state.selectionType === "Exit Port"){
            if (start === 0) {
              // First page - replace vehicles
              state.vehicles1 = action.payload;
             
            } else {
              // Subsequent pages - accumulate vehicles
              const existingIds = new Set(state.vehicles1.map(vehicle => vehicle.id));
              const newVehicles = action.payload.filter(vehicle => !existingIds.has(vehicle.id));
              state.vehicles1 = [...state.vehicles1, ...newVehicles];
              
            }
          }
        }
        

       
      })
      .addCase(fetchZoneVehicles.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload.message; // Save the error in state
        
      });

    builder
      .addCase(fetchLocalZone.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchLocalZone.fulfilled, (state, action) => {
        state.loading = false;
        state.zone = action.payload;
      })
      .addCase(fetchLocalZone.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
    
  },
});

export const {
  setentrypickup,
  setentrydropoff,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setexitpickupdate,
  setentrytime,
  setexittime,
  setadult,
  setvehicletype1,
  setchildren,
  settravellers1,
  updateFormData,
  updateFormData1,
  settourId,
  setentrydata,
  setexitdata,
  setEntryport,
  setExitport,
  setSelectedVehicle,
  setSelectedVehicle1,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  setPickupPlaceid1,
  setDropoffPlaceid1,
  setMode,
  setPriceMode,
  setResponse,
  resetVehicles,
  setbookingtype1,
  setSelectedPort,
  //setbookingType,
  setPicktype,
  setDroptype,
  setPortZoneType,
  setSelectedVehicleId,
  setAdultCount,
  setChildCount,
} = pickupDropSlice.actions;

export default pickupDropSlice.reducer;

import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { logoutUser } from "../common/authSlices";
import { BASE_URL } from "@/services/api";
import { selectDmcId } from "../dmc/dmcSlice";
import PickUpLocation from "@/components/hero/hero-8/PickUpLocation";
import DropOffLocation from "@/components/hero/hero-8/DropOffLocation";
// import { pick } from "lodash";
// import state from "sweetalert/typings/modules/state";
//import { format } from "date-fns";
export const fetchVehicles = createAsyncThunk(
  "localtour/fetchVehicles",
  async (params1 = {}, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state
      const { PickupPlaceid, DropoffPlaceid, pickdate, entrytime } =
        state.localtour;
      const selectedDmcId = selectDmcId(state);

      if (!PickupPlaceid) {
        throw new Error("Pickup location is required");
      }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      // ✅ Format pickdate as JSON { "0": "YYYY-MM-DD" }
      const travelDate = JSON.stringify({ 0: pickdate });

      // ✅ Build query parameters dynamically
      const params = {
        pickup: JSON.stringify(PickupPlaceid),
        time: JSON.stringify(entrytime),
        date: travelDate, // ✅ Include formatted date
        dmc_id: selectedDmcId,
        start: (params1 && params1.start) || undefined,
        limit: (params1 && params1.limit) || undefined,
      };

      if (DropoffPlaceid) {
        params.dropoff = JSON.stringify(DropoffPlaceid);
      }

      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/vehicles-list`, {
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
export const fetchZoneVehicles = createAsyncThunk(
  "localtour/fetchZoneVehicles",
  async (params1 = {}, { rejectWithValue, getState }) => {
    try {
      const state = getState(); // ✅ Get Redux state
      const {
        PickupZoneid,
        DropoffZoneid,
        entrypickup,
        entrydropoff,
        pickdate,
        entrytime,
        picktype,
        droptype,
      } = state.localtour;

      if (!PickupZoneid) {
        throw new Error("Pickup location is required");
      }

      if (!droptype) {
        console.error("Drop-off type is missing");
      }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
      const selectedDmcId = selectDmcId(state);
      // ✅ Format pickdate as JSON { "0": "YYYY-MM-DD" }
      const travelDate = JSON.stringify({ 0: pickdate });

      // ✅ Build query parameters dynamically
      const params = {
        pickupid: PickupZoneid,
        dropoffid: DropoffZoneid,
        PickUpLocation: entrypickup,
        DropOffLocation: entrydropoff,
        time: entrytime,
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

export const fetchVehicleDetails = createAsyncThunk(
  "localtour/fetchVehiclesDetails",
  async ({ city, country, type }, { rejectWithValue, getState, dispatch }) => {
    try {
      const state = getState();
      const {
        PickupPlaceid,
        PickupZoneid,
        DropoffZoneid,
        DropoffPlaceid,
        selectedVehicle,
        pickdate,
        entrytime,
        picktype,
        droptype,
      } = state.localtour;
      const selectedDmcId = selectDmcId(state);
      // ✅ Validate required fields
      // if (!PickupZoneid || !PickupPlaceid)
      //   throw new Error("Pickup location is required");

      const authToken = Cookies.get("authToken");
      const agentID = state.editing?.agentId;
      const userRole = state.auth?.userRole;

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

      // ✅ Build query parameters dynamically
      const travelDate = JSON.stringify({ 0: pickdate });
      const params =
        type === "zone"
          ? {
              from_zone_id: PickupZoneid,
              to_zone_id: DropoffZoneid,
              vehicle_id: selectedVehicle?.id,
              date: travelDate,
              time: JSON.stringify(entrytime),
              mode: selectedVehicle?.mode,
              dmc_id: selectedDmcId,
              pickup_type: picktype,
              drop_type: droptype,
              city,
              country,
              type
            }
          : {
              pickup: JSON.stringify(PickupPlaceid),
              date: travelDate,
              time: JSON.stringify(entrytime),
              vehicle_id: selectedVehicle?.id,
              mode: selectedVehicle?.mode,
              dmc_id: selectedDmcId,
              city,
              country,
              type,
              ...(DropoffPlaceid && {
                dropoff: JSON.stringify(DropoffPlaceid),
              }), // ✅ Add dropoff if available
            };

      // ✅ Make API request with dynamic params
      const response = await axios.get(`${BASE_URL}/vehicle-details`, {
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
        await dispatch(logoutUser());
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

// Async thunk for fetching hotels
export const Localtourslice = createAsyncThunk(
  "localtour/submit",
  async ({ selectedType }, { getState, dispatch, rejectWithValue }) => {
    try {
      const state = getState();
      let {
        entrypickup,
        entrydropoff,
        exitpickup,
        //exitdropoff,
        pickdate,
        bookingtype,
        entrytime,
        //exittime,
        entrytime1,
        hours,
        adult,
        vehicletype1,
        children,
        traveller1,
        type,
        type1,
        type2,
        details,
        details1,
        tourid,
      } = state.localtour;
     

     

      const authToken = Cookies.get("authToken");
      // Corrected conditional statement
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

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Create FormData instances
      let formData = {};
      let formData1 = {};

     

      // Populate FormData if entrypickup and related fields exist
      if (
        entrypickup &&
        entrydropoff &&
        pickdate &&
        entrytime &&
        adult !== null &&
        adult !== undefined &&
        children !== null &&
        children !== undefined
      ) {
      
        formData = {
          data: details.map(item => ({ ...item, dmc_id: selectedDmcId })),
          type: selectedType === "travelpointzone" ? type2 : type,
          agent_id: AgentId,
          tour_id: tourid,
          bookingType: bookingtype,
        };
      }

      if (
        exitpickup &&
        pickdate &&
        entrytime1 &&
        hours &&
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
      
      let selectedFormData = null;
      if (selectedType === "travelpoint" && "data" in formData) {
        selectedFormData = formData;
        // dispatch(setentrypickup(null)); // Reset state after form selection
        // dispatch(setentrydropoff([]));
        // dispatch(setpickdate(null));
        // dispatch(setentrytime(null));
        // dispatch(setadult(null));
        // dispatch(setchildren(0));
        dispatch(setpointdata([]));
      } else if (selectedType === "travelpointzone" && "data" in formData) {
        selectedFormData = formData;
        dispatch(setpointdata([]));
      } else if (selectedType === "travelhourly" && "data" in formData1) {
        selectedFormData = formData1;
        // dispatch(setexitpickup(null)); // Reset state after form selection
        // dispatch(setpickdate(null));
        // dispatch(setentrytime1(null));
        // dispatch(sethour(null));
        // dispatch(setadult(null));
        // dispatch(setchildren(0));
        dispatch(sethourlydata([]));
      } else {
        throw new Error("No valid data to submit.");
      }
     

     

      // Uncomment the code below for the actual API call

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

      console.log("API Response:", response.data);

      // Check if response has the expected structure
      if (!response.data || !response.data.service) {
        console.error("Invalid response structure:", response.data);
        return response.data;
      }

      // Extracting response data - CORRECTED to use service.data
      const responseData = response.data.service.data || [];
     
      // Check if responseData exists and is an array
      if (!Array.isArray(responseData)) {
        console.error(
          "Expected responseData to be an array but got:",
          responseData
        );

        // Set defaults
        const travelPointData = [];
        const travelHourlyData = [];

     

        // Return the response anyway for error handling
        return response.data;
      }

      // Improved filtering with multiple conditions
      const travelPointData = responseData.filter((item) => {
        // First, prioritize the service type
        if (response.data.service.type === "travel_point") {
          return true;
        }

        // Then check if the item explicitly mentions travel_point in its type
        if (
          item.type &&
          typeof item.type === "string" &&
          (item.type.includes("point") || item.type === "travel_point")
        ) {
          return true;
        }

        // Skip anything that explicitly mentions zone or hourly
        if (
          item.type &&
          typeof item.type === "string" &&
          (item.type.includes("local_transport") ||
            item.type === "local_transport" ||
            item.type.includes("hourly") ||
            item.type === "travel_hourly")
        ) {
          return false;
        }

        return false; // Default to false for safety
      });

      const travelHourlyData = responseData.filter((item) => {
        // First, prioritize the service type
        if (response.data.service.type === "travel_hourly") {
          return true;
        }

        // Check for selectedHours property or hours property
        if (item.selectedHours !== undefined || item.hours) {
          return true;
        }

        // Check for explicit type
        if (
          item.type &&
          typeof item.type === "string" &&
          (item.type.includes("hourly") || item.type === "travel_hourly")
        ) {
          return true;
        }

        return false; // Default to false for safety
      });

      const travelZoneData = responseData.filter((item) => {
        // First, prioritize the service type
        if (response.data.service.type === "zone") {
          return true;
        }

        // Then check if the item explicitly mentions zone in its type
        if (
          item.type &&
          typeof item.type === "string" &&
          (item.type.includes("local_transport") ||
            item.type === "local_transport")
        ) {
          return true;
        }

        return false; // Default to false for safety
      });

    

      // Use the service type as fallback
      if (
        response.data.service.type === "travel_point" &&
        responseData.length > 0
      ) {
        dispatch(setPointToPoint(responseData));
       
      } else if (
        response.data.service.type === "travel_hourly" &&
        responseData.length > 0
      ) {
        dispatch(setHourly(responseData));
     
      } else if (
        response.data.service.type === "local_transport" &&
        responseData.length > 0
      ) {
        dispatch(setZone(responseData));
      } else {
        // Apply the filter logic as fallback
        if (travelPointData.length > 0) {
          dispatch(setPointToPoint(travelPointData));
        }

        if (travelHourlyData.length > 0) {
          dispatch(setHourly(travelHourlyData));
        }
        if (travelZoneData.length > 0) {
          dispatch(setZone(travelZoneData));
        }
      }

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
export const fetchLocalZone = createAsyncThunk(
  "localtour/fetchLocalZone",
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
        userRole === "Assistant Manager (DMC)"
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

      console.log("Local Zone Response:", response.data);

      return response.data;
    } catch (error) {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Dispatching logout...");
        dispatch(logoutUser());
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const LocalSlice = createSlice({
  name: "localtour",
  initialState: {
    status: "idle", // Tracks the status: 'idle', 'loading', 'succeeded', 'failed'
    error: null, // Stores any errors from API requests
    entrypickup: "",
    entrydropoff: "",
    exitpickup: "",
    //exitdropoff: "",
    pickupdate: null,
    exitpickupdate: "",
    entrytime: "",
    //exittime: "",
    entrytime1: "",
    hours: "",
    adult: 0,
    vehicletype1: "",
    children: 0,
    traveller1: 0,
    type: "travel_point",
    type1: "travel_hourly",
    type2: "local_transport",
    tourid: 0,
    details: [],
    details1: [],
    pointtopoint: [],
    hourly: [],
    Zonebook: [],
    PickupPlaceid: "",
    DropoffPlaceid: "",
    PickupZoneid: "",
    PickupZoneid: "",
    selectionType: "",
    vehicles: [],
    pickdate: "",
    exitpickdate: "",
    mode: {},
    selectedVehicle: {},
    pricemode: "",
    bookingtype: "",
    selectedPort: "",
    port: "",
    zone: [],
    picktype: "",
    droptype: "",
    selectbooking: [],
    zonetype: "",
    adultCount: 0,
    childCount: 0,
    hourCount: 4,
    pickupLocation: "",
    dropLocation: "",
    roundTrip: false,
    searchDayIndex: null, // Track which dayIndex initiated the search
  },
  reducers: {
    setentrypickup: (state, action) => {
      state.entrypickup = action.payload;
      console.log("entry", state.entrypickup);
    },
    setentrydropoff: (state, action) => {
      state.entrydropoff = action.payload;
      console.log("exit", state.entrydropoff);
    },
    setexitpickup: (state, action) => {
      state.exitpickup = action.payload;
    },
    //   setexitdropoff: (state, action) => {
    //     state.exitdropoff = action.payload;
    //   },
    setpickupdate: (state, action) => {
      state.pickupdate = action.payload;
    
    },
    setpickdate: (state, action) => {
      // Check if payload is a Moment object and convert to string if it is
      if (action.payload && action.payload._isAMomentObject) {
        state.pickdate = action.payload.format('YYYY-MM-DD'); // Convert to YYYY-MM-DD format string
      } else {
        state.pickdate = action.payload;
      }
  
    },

    setexitpickupdate: (state, action) => {
      state.exitpickupdate = action.payload;
 
    },
    setentrytime: (state, action) => {
      state.entrytime = action.payload;
   
    },
    setPickupPlaceid: (state, action) => {
      state.PickupPlaceid = action.payload;
      
    },
    setDropoffPlaceid: (state, action) => {
      state.DropoffPlaceid = action.payload;
     
    },
    setPickupZoneid: (state, action) => {
      state.PickupZoneid = action.payload;
      
    },
    setDropoffZoneid: (state, action) => {
      state.DropoffZoneid = action.payload;
     
    },
    setPriceMode1: (state, action) => {
      state.pricemode = action.payload;
     
    },
    setbookingtype3: (state, action) => {
      state.bookingtype = action.payload;
     
    },
    // setexittime: (state, action) => {
    //   state.exittime = action.payload;
    // },
    setentrytime1: (state, action) => {
      state.entrytime1 = action.payload;
      
    },
    sethour: (state, action) => {
      state.hours = action.payload;
    },
    setadult: (state, action) => {
      state.adult = action.payload;
    
    },
    setchildren: (state, action) => {
      state.children = action.payload;
     
    },
    setAdultCount: (state, action) => {
      state.adultCount = action.payload;
    },
    setChildCount: (state, action) => {
      state.childCount = action.payload;
    },
    setHourCount: (state, action) => {
      state.hourCount = action.payload;
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
    setpointdata: (state, action) => {
      if (!Array.isArray(state.details)) {
        state.details = []; // Ensure details is an array
      }
      if (state.details[0]) {
        state.details[0] = { ...state.details[0], ...action.payload }; // ✅ Merge with existing data
      } else {
        state.details[0] = action.payload; // ✅ Insert if empty
      }
    },
    sethourlydata: (state, action) => {
      if (!Array.isArray(state.details1)) {
        state.details1 = []; // Ensure details is an array
      }
      if (state.details1[0]) {
        state.details1[0] = { ...state.details1[0], ...action.payload }; // Append new data instead of overriding
      } else {
        state.details1[0] = action.payload; // ✅ Insert if empty
      }
     
    },
    setPointToPoint: (state, action) => {
      // Append new form data to the existing pointtopoint array
      state.pointtopoint = action.payload;
     
    },
    setHourly: (state, action) => {
      // Append new form data to the existing hourly array
      state.hourly = action.payload;
     
    },
    setZone: (state, action) => {
      // Append new form data to the existing pointtopoint array
      state.Zonebook = action.payload;
     
    },
    setSelectionType: (state, action) => {
      state.selectionType = action.payload;
      
    },
    setMode: (state, action) => {
      state.mode = { ...state.mode, ...action.payload }; // ✅ Merge new mode
      
    },
    setSelectedVehicle: (state, action) => {
      state.selectedVehicle = action.payload;
      
    },
    setSelectedPort: (state, action) => {
      state.selectedPort = action.payload;
      console.log("selectedPort", state.selectedPort);
    },
    setPort: (state, action) => {
      state.port = action.payload;
      
    },
    resetVehicles1: (state, action) => {
      state.vehicles = [];
      state.entrypickup = "";
      state.entrydropoff = "";
      state.exitpickup = "";
      state.pickupdate = "";
      state.exitpickupdate = "";
      state.entrytime = "";
      state.DropoffPlaceid = "";
      state.PickupPlaceid = "";
    
      state.PickupZoneid = "";
      state.DropoffZoneid = "";
      //state.selectedVehicleId = null;
      state.status = "idle";
      state.error = null;
    },
    setPicktype: (state, action) => {
      state.picktype = action.payload;
      console.log("picktype", state.picktype);
    },
    setSelectbooking: (state, action) => {
      state.selectbooking = action.payload;
      console.log("selectbooking", state.selectbooking);
    },
    setDroptype: (state, action) => {
      state.droptype = action.payload;
      console.log("droptype", state.droptype);
    },
    setZonetype: (state, action) => {
      state.zonetype = action.payload;
    },
    setSearchDayIndex: (state, action) => {
      state.searchDayIndex = action.payload;
      console.log("searchDayIndex set to:", state.searchDayIndex);
    },
    clearSearchDayIndex: (state) => {
      state.searchDayIndex = null;
      console.log("searchDayIndex cleared");
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
        if(action.payload && typeof action.payload === 'object' && action.payload.success === false){
         
          state.vehicles = [];
          state.error = action.payload.message;
         
        } else {
          // Support infinite scrolling - check if it's initial load or subsequent load
          const { start = 0 } = action.meta?.arg || {};
          
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
       
      })
      .addCase(fetchVehicles.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload.message; // Save the error in state
        
      });

    builder
      .addCase(fetchZoneVehicles.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchZoneVehicles.fulfilled, (state, action) => {
        state.status = "succeeded";
        if(action.payload && typeof action.payload === 'object' && action.payload.success === false){
         
          state.vehicles = [];
          state.error = action.payload.message;
       
        } else {
          // Support infinite scrolling - check if it's initial load or subsequent load
          const { start = 0 } = action.meta?.arg || {};
          
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

       
      })
      .addCase(fetchZoneVehicles.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload.message; // Save the error in state
       
      });

    // ... (other extraReducers)
    // Add extraReducers for fetchLocalZone
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
  //setexitdropoff,
  setpickupdate,
  setexitpickupdate,
  setentrytime,
  //setexittime,
  setentrytime1,
  sethour,
  setvehicletype,
  setvehicletype1,
  settravellers0,
  settravellers1,
  settourId,
  setpointdata,
  sethourlydata,
  setPointToPoint,
  setHourly,
  setPickupPlaceid,
  setDropoffPlaceid,
  setadult,
  setchildren,
  setSelectionType,
  setSelectedVehicle,
  setpickdate,
  setMode,
  setPriceMode1,
  resetVehicles1,
  setbookingtype3,
  setSelectedPort,
  setPort,
  setPicktype,
  setDroptype,
  setSelectbooking,
  setPickupZoneid,
  setDropoffZoneid,
  setZonetype,
  setZone,
  setAdultCount,
  setChildCount,
  setHourCount,
  setSearchDayIndex,
  clearSearchDayIndex,
} = LocalSlice.actions;

export default LocalSlice.reducer;
